<?php

namespace Command\Build;

use Command\Command;
use Command\Config;
use RuntimeException;

class Build extends Command
{

  public static function getShortDescription(): string
  {
    return 'Build all enabled commands.';
  }

  public static function getDescription(): array
  {
    return [static::getShortDescription()];
  }

  public static function getUsage(): array
  {
    return ['fs build'];
  }

  public static function getExamples(): array
  {
    return [];
  }

  public function call(): void
  {
    $this->build($this->checkDependencies($this->getAllCommands()));
  }

  /**
   * @return Config[]
   */
  private function getAllCommands(): array
  {
    $list = [];

    foreach (scandir($this->cwd) as $domain)
    {
      if (in_array($domain, ['.', '..', '.git', '.github', 'bin', 'fs']))
      {
        continue;
      }
      if (is_dir("$this->cwd/$domain"))
      {
        foreach (scandir("$this->cwd/$domain") as $component)
        {
          if (in_array($component, ['.', '..', '.git']))
          {
            continue;
          }
          if (is_dir("$this->cwd/$domain/$component"))
          {
            $config = new Config("$this->cwd/$domain/$component/commands.json");

            foreach ($config->getEnabledCommands() as $command)
            {
              foreach ($config->getVersions($command) as $version)
              {
                $list["$domain/$component/$command:$version"] = $config->getVersionConfig($command, $version);
              }
            }
          }
        }
      }
    }

    return $list;
  }

  private function checkDependencies(array $list): array
  {
    $depList = [];

    foreach ($list as $command => $config)
    {
      if (in_array($command, $depList))
      {
        continue;
      }
      $depList[] = $command;
      if (!key_exists('from', $config))
      {
        continue;
      }
      $deps = $config['from'];
      while (count($deps))
      {
        $dep = array_shift($deps);
        if (!$this->checkFormat($dep))
        {
          throw new RuntimeException("Dependency `$dep` in `$command` is not a valid dependency.");
        }
        $fullname = $this->getDepencyFullname($dep, $command);

        if (in_array($fullname, $depList))
        {
          throw new RuntimeException("Circular dependency detected in `$fullname`.");
        }

        if (!key_exists($fullname, $list))
        {
          throw new RuntimeException("Command `$fullname` not found for `$command`.");
        }

        $depList[] = $fullname;
        $depConfig = $list[$fullname];
        if (key_exists('from', $depConfig))
        {
          foreach ($depConfig['from'] as $dep)
          {
            if (in_array($dep, $deps))
            {
              continue;
            }

            $deps[] = $dep;
          }
        }
      }
    }

    return $list;
  }

  private function checkFormat(string $name): bool
  {
    if (count(explode(':', $name)) > 2)
    {
      return false;
    }
    if (count(explode('/', explode(':', $name)[0])) > 3)
    {
      return false;
    }

    return true;
  }

  private function getDepencyFullname(string $name, string $parent): string
  {
    [$longName, $version] = explode(':', $parent);
    [$domain, $component, $command] = explode('/', $longName);

    if (count(explode(':', $name)) === 1)
    {
      $name .= ":$version";
    }

    [$depLongName, $depVersion] = explode(':', $name);

    return match (count(explode('/', $depLongName)))
    {
      1 => "$domain/$component/$depLongName:$depVersion",
      2 => "$domain/$depLongName:$depVersion",
      3 => "$depLongName:$depVersion",
    };
  }

  private function buildCommand(array &$list, string $command, array &$built): string
  {
    $buildFile = '';

    $config = $list[$command];

    if (key_exists('from', $config))
    {
      foreach ($config['from'] as $dependency)
      {
        $buildFile .= $this->buildCommand($list, $this->getDepencyFullname($dependency, $command), $built);
      }
    }

    $name = explode(':', $command)[0];
    $version = explode(':', $command)[1];
    $context = implode('/', array_slice(explode('/', $name), 0, 2));

    $buildFile .= "name=$name" . PHP_EOL;
    $buildFile .= "version=$version" . PHP_EOL;
    $buildFile .= "context=$context" . PHP_EOL;

    foreach ($config['arguments'] as $argument => $value)
    {
      $buildFile .= "argument=$argument:$value" . PHP_EOL;
    }
    $buildFile .= 'build' . PHP_EOL;

    $built[] = $command;

    return $buildFile;
  }

  private function buildFiles(array $list): void
  {
    sort($list);

    foreach ($list as $path)
    {
      if ($this->fw->exists("$path/cache"))
      {
        $this->fw->cleanDir("$path/cache");
      }

      if ($this->fw->exists("$path/files"))
      {
        $this->fw->copy("$path/files", "$path/cache", true);
      }

      if ($this->fw->exists("$path/local"))
      {
        $this->fw->copy("$path/local", "$path/cache", true);
      }
    }
  }

  private function build(array $list): void
  {
    $buildFile = '';
    $built = [];
    foreach ($list as $command => $config)
    {
      if (!in_array($command, $built))
      {
        $buildFile .= $this->buildCommand($list, $command, $built);
      }
    }

    $this->buildFiles(array_unique(array_map(function ($command) {
      return implode('/', array_slice(explode('/', explode(':', $command)[0]), 0, 2));
    }, array_keys($list))));

    $list = array_map(function ($cmd) {
      return array_filter($cmd, function ($key) {
        return in_array($key, [
          'env',
          'volumes',
          'ports',
          'interactive',
          'detached',
          'match-ids',
          'workdir',
          'command',
        ]);
      }, ARRAY_FILTER_USE_KEY);
    }, $list);
    if (
      @file_put_contents("$this->cwd/build.cache", $buildFile, LOCK_EX) === false ||
      @file_put_contents("$this->cwd/commands.cache", json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) === false)
    {
      throw new RuntimeException('Unable to create cache files.');
    }
    echo count(array_keys($list)) . ' commands to build.' . PHP_EOL;
  }
}
