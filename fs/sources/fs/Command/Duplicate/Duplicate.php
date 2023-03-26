<?php

namespace Command\Duplicate;

use Command\Command;
use Command\Config;
use Command\Create\Create;
use Exception;
use RuntimeException;

class Duplicate extends Command
{

  public static function getShortDescription(): string
  {
    return 'Duplicate a domain, component or command.';
  }

  public static function getDescription(): array
  {
    return [static::getShortDescription()];
  }

  public static function getUsage(): array
  {
    return ['fs duplicate [domain[/component[/command]]] [domain[/component[/command]]]'];
  }

  public static function getExamples(): array
  {
    return [
      'fs duplicate foo bar',
      'fs duplicate foo/bar bar/bar',
      'fs duplicate foo/bar/foo foo/bar/bar',
    ];
  }

  /**
   * @throws Exception
   */
  public function call(): void
  {
    if ($this->argc !== 2)
    {
      $this->usage();
    }

    $left = explode('/', $this->argv[0]);
    $right = explode('/', $this->argv[1]);

    if (count($left) !== count($right))
    {
      throw new RuntimeException('Each member must have the same level.');
    }

    switch (count($left))
    {
      case 1:
        $this->duplicateDomain($left, $right);
        break;
      case 2:
        $this->duplicateComponent($left, $right);
        break;
      case 3:
        $this->duplicateCommand($left, $right);
        break;
      default:
        throw new RuntimeException('Unknown level.');
    }
  }

  private function duplicateDomain(array $left, array $right): void
  {
    if (!is_dir("$this->cwd/{$left[0]}"))
    {
      throw new RuntimeException("Domain `{$left[0]}` does not exist.");
    }

    if (is_dir("$this->cwd/{$right[0]}"))
    {
      throw new RuntimeException("Domain `{$right[0]}` already exist.");
    }

    $this->copy("$this->cwd/{$left[0]}", "$this->cwd/{$right[0]}");
    echo "Domain `{$left[0]}` duplicate to `{$right[0]}`." . PHP_EOL;
  }

  private function duplicateComponent(array $left, array $right): void
  {
    if (!is_dir("$this->cwd/{$left[0]}/{$left[1]}"))
    {
      throw new RuntimeException("Component `{$left[0]}/{$left[1]}` does not exist.");
    }

    if (is_dir("$this->cwd/{$right[0]}/{$right[1]}"))
    {
      throw new RuntimeException("Component `{$right[0]}/{$right[1]}` already exist.");
    }

    $this->copy("$this->cwd/{$left[0]}/{$left[1]}", "$this->cwd/{$right[0]}/{$right[1]}");
    echo "Component `{$left[0]}/{$left[1]}` duplicate to `{$right[0]}/{$right[1]}`." . PHP_EOL;
  }

  private function copy(string $from, string $to): void
  {
    if (!file_exists($from))
    {
      throw new RuntimeException("`$from` does not exist.");
    }

    if (file_exists($to))
    {
      throw new RuntimeException("`$to` already exist.");
    }

    if (!is_dir($from))
    {
      if (!copy($from, $to))
      {
        throw new RuntimeException("Unable to copy `$from` to `$to`.");
      }
    }
    else
    {
      if (!mkdir($to, 0700))
      {
        throw new RuntimeException("Unable to copy `$from` to `$to`.");
      }

      foreach (scandir($from) as $d)
      {
        if (in_array($d, ['.', '..']))
        {
          continue;
        }
        $this->copy("$from/$d", "$to/$d");
      }
    }
  }

  /**
   * @throws Exception
   */
  private function duplicateCommand(array $left, array $right): void
  {
    if (!file_exists("$this->cwd/{$left[0]}/{$left[1]}/commands.json"))
    {
      throw new RuntimeException("Component `{$left[0]}/{$left[1]}` does not exist.");
    }

    if (($left[0] !== $right[0]) || ($left[1] !== $right[1]))
    {
      $this->duplicateNewCommand($left, $right);
      return;
    }

    $config = new Config("$this->cwd/{$left[0]}/{$left[1]}/commands.json");
    if (!$config->hasCommand($left[2]))
    {
      throw new RuntimeException("Command `{$left[0]}/{$left[1]}/{$left[2]}` does not exist.");
    }
    if ($config->hasCommand($right[2]))
    {
      throw new RuntimeException("Command `{$right[0]}/{$right[1]}/{$right[2]}` already exist.");
    }

    $config->setCommand($right[2], $config['commands'][$left[2]])->save();
    echo "Command `{$left[0]}/{$left[1]}/{$left[2]}` duplicate to `{$right[0]}/{$right[1]}/{$right[2]}`." . PHP_EOL;
  }

  /**
   * @throws Exception
   */
  private function duplicateNewCommand(array $left, array $right): void
  {
    if (!file_exists("$this->cwd/{$right[0]}/{$right[1]}/commands.json"))
    {
      Command::callCommand(Create::class, [implode('/', $right)], true);
      $rightFile = new Config("$this->cwd/{$right[0]}/{$right[1]}/commands.json");
      $rightFile['default'] = (new Config("$this->cwd/{$left[0]}/{$left[1]}/commands.json"))['default'];
      $rightFile->save();
    }
    else
    {
      $rightFile = new Config("$this->cwd/{$right[0]}/{$right[1]}/commands.json");
      if ($rightFile->hasCommand($right[2]))
      {
        throw new RuntimeException("Command `{$right[0]}/{$right[1]}/{$right[2]}` already exist.");
      }
    }

    $leftFile = new Config("$this->cwd/{$left[0]}/{$left[1]}/commands.json");
    $rightFile = new Config("$this->cwd/{$right[0]}/{$right[1]}/commands.json");
    if (!$leftFile->hasCommand($left[2]))
    {
      throw new RuntimeException("Command `{$left[0]}/{$left[1]}/{$left[2]}` does not exist.");
    }

    $rightFile->setCommand($right[2], $leftFile->getCommand($left[2]))->save();
    echo "Command `{$left[0]}/{$left[1]}/{$left[2]}` duplicate to `{$right[0]}/{$right[1]}/{$right[2]}`." . PHP_EOL;
  }
}
