<?php

namespace Command\Duplicate;

use Command\Command;
use Command\Config;
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

  }

  private function duplicateComponent(array $left, array $right): void
  {

  }

  private function duplicateCommand(array $left, array $right): void
  {
    if (!file_exists("$this->cwd/${left[0]}/${left[1]}/commands.json"))
    {
      throw new RuntimeException("Component `${left[0]}/${left[1]}` does not exist.");
    }

    $config = new Config("$this->cwd/${left[0]}/${left[1]}/commands.json");
    if (!$config->offsetExists($left[2]))
    {
      throw new RuntimeException("Command `${left[0]}/${left[1]}/${left[2]}` does not exist.");
    }

    $config->merge([$right[2] => $config[$left[2]]])->save();
    echo "Command `${left[0]}/${left[1]}/${left[2]}` duplicate to `${right[0]}/${right[1]}/${right[2]}`." . PHP_EOL;
  }
}
