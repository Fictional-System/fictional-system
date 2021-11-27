<?php

namespace Command\Build;

use Command\Command;
use Command\Config;
use Command\FileWrapper;
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
      if (in_array($domain, FileWrapper::IGNORED_ROOT_FILES))
      {
        continue;
      }
      if (is_dir("$this->cwd/$domain"))
      {
        foreach (scandir("$this->cwd/$domain") as $component)
        {
          if (in_array($component, FileWrapper::IGNORED_FILES))
          {
            continue;
          }
          if (is_dir("$this->cwd/$domain/$component"))
          {
            $config = new Config("$this->cwd/$domain/$component/commands.json");

            foreach ($config->getTags() as $tag)
            {
              $list["$domain/$component:$tag"] = ['build' => $config->getBuildConfig($tag), 'commands' => []];
              foreach ($config->getEnabledCommands() as $command)
              {
                $list["$domain/$component:$tag"]['commands'][$command] = $config->getTagConfig($command, $tag);
              }

              if (count($list["$domain/$component:$tag"]['commands']) === 0)
              {
                unset($list["$domain/$component:$tag"]);
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

    foreach ($list as $component => $config)
    {
      $componentDeps = [];
      if (in_array($component, $depList))
      {
        continue;
      }
      $depList[] = $component;
      if (!key_exists('from', $config['build']))
      {
        continue;
      }
      $deps = $config['build']['from'];
      while (count($deps))
      {
        $dep = array_shift($deps);
        if (!$this->checkFormat($dep))
        {
          throw new RuntimeException("Dependency `$dep` in `$component` is not a valid dependency.");
        }
        $fullname = $this->getDepencyFullname($dep, $component);
        if (in_array($fullname, $componentDeps))
        {
          throw new RuntimeException("Circular dependency detected in `$component`.");
        }

        if (!key_exists($fullname, $list))
        {
          throw new RuntimeException("Component `$fullname` not found for `$component`.");
        }

        if (!in_array($fullname, $depList))
        {
          $depList[] = $fullname;
        }
        $componentDeps[] = $fullname;
        $depConfig = $list[$fullname];
        if (key_exists('from', $depConfig['build']))
        {
          foreach ($depConfig['build']['from'] as $dep)
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
    if (count(explode('/', explode(':', $name)[0])) > 2)
    {
      return false;
    }

    return true;
  }

  private function getDepencyFullname(string $name, string $parent): string
  {
    [$longName, $tag] = explode(':', $parent);
    $domain = explode('/', $longName)[0];

    if (count(explode(':', $name)) === 1)
    {
      $name .= ":$tag";
    }

    [$depLongName, $depTag] = explode(':', $name);

    return match (count(explode('/', $depLongName)))
    {
      1 => "$domain/$depLongName:$depTag",
      2 => "$depLongName:$depTag",
    };
  }

  private function buildComponent(array &$list, string $component, array &$built): string
  {
    $buildFile = '';

    if (in_array($component, $built))
    {
      return $buildFile;
    }

    $config = $list[$component]['build'];

    if (key_exists('from', $config))
    {
      foreach ($config['from'] as $dependency)
      {
        $buildFile .= $this->buildComponent($list, $this->getDepencyFullname($dependency, $component), $built);
      }
    }

    [$name, $tag] = explode(':', $component);

    $buildFile .= "name=$name" . PHP_EOL;
    $buildFile .= "tag=$tag" . PHP_EOL;

    foreach ($config['arguments'] as $argument => $value)
    {
      $buildFile .= "argument=$argument $value" . PHP_EOL;
    }
    $buildFile .= 'build' . PHP_EOL;

    $built[] = $component;

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
    foreach ($list as $component => $config)
    {
      $buildFile .= $this->buildComponent($list, $component, $built);
    }

    $this->buildFiles(array_unique(array_map(function ($component) {
      return explode(':', $component)[0];
    }, array_keys($list))));

    $list = array_map(function ($component) {
      return array_map(function ($command) {
        return array_filter($command, function ($value, $key) {
          return in_array($key, Config::FILTER_CONFIG_KEYS['cache']);
        }, ARRAY_FILTER_USE_BOTH);
      }, $component['commands']);
    }, $list);
    if (
      @file_put_contents("$this->cwd/build.cache", trim($buildFile) . PHP_EOL, LOCK_EX) === false ||
      @file_put_contents("$this->cwd/commands.cache", json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL, LOCK_EX) === false)
    {
      throw new RuntimeException('Unable to create cache files.');
    }

    $count = array_reduce($list, function ($sum, $cmp) {
      return $sum + count($cmp);
    }, 0);
    echo $count . ' commands to build.' . PHP_EOL;
  }
}
