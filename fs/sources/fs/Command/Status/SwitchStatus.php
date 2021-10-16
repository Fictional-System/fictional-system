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
    if ($component === null)
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
    else
    {
      $this->switchComponent($enable, $domain, $component, $command);
    }
  }

  private function switchComponent(bool $enable, string $domain, string $component, string $command = null): void
  {
    if ($command === null)
    {
      $config = new Config("$this->cwd/$domain/$component/commands.json");
      foreach ($config as $key => $value)
      {
        if (in_array($key, ['default']))
        {
          continue;
        }
        $this->switchCommand($enable, $domain, $component, $key);
      }
    }
    else
    {
      $this->switchCommand($enable, $domain, $component, $command);
    }
  }

  private function switchCommand(bool $enable, string $domain, string $component, string $command): void
  {
    $config = new Config("$this->cwd/$domain/$component/commands.json");
    if (!$config->offsetExists($command))
    {
      throw new RuntimeException('Cannot ' . ($enable ? 'en' : 'dis') . 'able `$domain/$component/$command`');
    }

    $config->switchStatus($command, $enable)->save();
    echo "Command `$domain/$component/$command` has been " . ($enable ? 'en' : 'dis') . 'abled.' . PHP_EOL;
  }
}
