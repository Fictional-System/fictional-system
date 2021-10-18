<?php

namespace Command\Status;

use Command\Command;
use Command\Config;
use RuntimeException;

abstract class SwitchStatus extends Command
{
  protected function switch(bool $enable, string $domain = null, string $component = null, string $command = null): void
  {
    if ($domain === null)
    {
      throw new RuntimeException('You must specify at least one domain.');
    }

    if ($domain === 'all')
    {
      foreach (scandir($this->cwd) as $dir)
      {
        if (in_array($dir, ['.', '..', '.git', 'bin', 'fs']))
        {
          continue;
        }
        if (is_dir("$this->cwd/$dir"))
        {
          $this->switchDomain($enable, $dir);
        }
      }
    }
    else
    {
      $this->switchDomain($enable, $domain, $component, $command);
    }
  }

  private function switchDomain(bool $enable, string $domain, string $component = null, string $command = null): void
  {
    if (($component === null) && is_dir("$this->cwd/$domain"))
    {
      foreach (scandir("$this->cwd/$domain") as $dir)
      {
        if (in_array($dir, ['.', '..', '.git']))
        {
          continue;
        }
        if (is_dir("$this->cwd/$domain/$dir"))
        {
          $this->switchComponent($enable, $domain, $dir);
        }
      }
    }
    else if (!is_dir("$this->cwd/$domain"))
    {
      throw new RuntimeException("Domain `$domain` does not exist.");
    }
    else
    {
      $this->switchComponent($enable, $domain, $component, $command);
    }
  }

  private function switchComponent(bool $enable, string $domain, string $component, string $command = null): void
  {
    if (($command === null) && is_dir("$this->cwd/$domain/$component"))
    {
      $config = new Config("$this->cwd/$domain/$component/commands.json");
      foreach ($config->getCommandNames() as $name)
      {
        $this->switchCommand($enable, $domain, $component, $name);
      }
    }
    else if (!is_dir("$this->cwd/$domain/$component"))
    {
      throw new RuntimeException("Component `$domain/$component` does not exist.");
    }
    else
    {
      $this->switchCommand($enable, $domain, $component, $command);
    }
  }

  private function switchCommand(bool $enable, string $domain, string $component, string $command): void
  {
    $config = new Config("$this->cwd/$domain/$component/commands.json");
    if (!$config->hasCommand($command))
    {
      throw new RuntimeException("Command `$domain/$component/$command` does not exist.");
    }

    $config->switchStatus($command, $enable)->save();
    echo "Command `$domain/$component/$command` has been " . ($enable ? 'en' : 'dis') . 'abled.' . PHP_EOL;
  }
}
