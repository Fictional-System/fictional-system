<?php

namespace Command\Create;

use Command\Command;
use Command\Config;
use RuntimeException;

class Create extends Command
{
  public static function getShortDescription(): string
  {
    return 'Create a domain, component or command.';
  }

  public static function getDescription(): array
  {
    return [static::getShortDescription()];
  }

  public static function getUsage(): array
  {
    return ['fs create [domain[/component[/command]]]'];
  }

  public static function getExamples(): array
  {
    return [
      'fs create foo',
      'fs create foo/bar',
      'fs create foo/bar/test',
      'fs create foo/bar/test bar/foo/test',
    ];
  }

  public function call(): void
  {
    if ($this->argc === 0)
    {
      $this->usage();
    }

    foreach ($this->argv as $name)
    {
      $this->createDomain(...explode('/', $name));
    }
  }

  private function createDomain(string $domain = null, string $component = null, string $command = null): void
  {
    if ($domain === null)
    {
      throw new RuntimeException('You must specify at least one domain.');
    }

    $this->createDir("$this->cwd/$domain", boolval($component));

    if ($component)
    {
      $this->createComponent($domain, $component, $command);
    }
    else
    {
      echo "Domain `$domain` has been created." . PHP_EOL;
    }
  }

  private function createComponent(string $domain, string $component = null, string $command = null): void
  {
    if ($component === null)
    {
      throw new RuntimeException('You must specify at least one component.');
    }

    $this->createDir("$this->cwd/$domain/$component", boolval($command));
    $this->createDir("$this->cwd/$domain/$component/files", boolval($command));
    $this->createFile("$this->cwd/$domain/$component/Containerfile", boolval($command));

    if (!file_exists("$this->cwd/$domain/$component/commands.json"))
    {
      Config::createTemplate("$this->cwd/$domain/$component/commands.json");
    }
    elseif (is_dir("$this->cwd/$domain/$component/commands.json"))
    {
      throw new RuntimeException("Unable to create `$domain/$component/commands.json`. A directory has the same name.");
    }

    if ($command)
    {
      $this->createCommand($domain, $component, $command);
    }
    else
    {
      echo "Component `$domain/$component` has been created." . PHP_EOL;
    }
  }

  private function createCommand(string $domain, string $component, string $command = null): void
  {
    $config = new Config("$this->cwd/$domain/$component/commands.json");

    if ($config->hasCommand($command))
    {
      throw new RuntimeException("Command `$domain/$component/$command` already exist.");
    }
    $config->createCommand($command)->save();

    echo "Command `$domain/$component/$command` has been created." . PHP_EOL;
  }

  private function createDir(string $dir, bool $force = false): void
  {
    if (file_exists($dir))
    {
      if (!is_dir($dir))
      {
        throw new RuntimeException("Unable to create `$dir`. A file has the same name.");
      }

      if (!$force)
      {
        throw new RuntimeException("`$dir` already exist.");
      }
    }

    if (!file_exists($dir) && !is_dir($dir) && !mkdir($dir, 0700))
    {
      throw new RuntimeException("Unable to create `$dir`");
    }
  }

  private function createFile(string $file, bool $force = false, string $content = ''): void
  {
    if (file_exists($file))
    {
      if (is_dir($file))
      {
        throw new RuntimeException("Unable to create `$file`. A directory has the same name.");
      }

      if (!$force)
      {
        throw new RuntimeException("`$file` already exist.");
      }
    }
    else if (@file_put_contents($file, $content) === false)
    {
      throw new RuntimeException("Unable to create `$file`");
    }
  }
}
