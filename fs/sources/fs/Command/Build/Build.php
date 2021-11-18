<?php

namespace Command\Build;

use Command\Command;
use Command\Config;

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
    return $list;
  }

  private function build(array $list): void
  {
    $buildFile = '';
    foreach ($list as $command => $config)
    {
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
    }

    echo count(array_keys($list)) . ' commands to build.' . PHP_EOL;
    file_put_contents('build.cache', $buildFile, LOCK_EX);
  }
}
