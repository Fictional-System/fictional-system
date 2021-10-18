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
    foreach ($this->getAllEnabledCommands() as $path => $commands)
    {
      foreach ($commands as $command)
      {
        var_dump(["$path/$command"]);
      }
    }
  }

  private function getAllEnabledCommands(): array
  {
    $list = [];

    foreach (scandir($this->cwd) as $domain)
    {
      if (in_array($domain, ['.', '..', '.git', 'bin', 'fs']))
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
            $list["$domain/$component"] = $config->getEnabledCommands();
          }
        }
      }
    }

    return $list;
  }
}
