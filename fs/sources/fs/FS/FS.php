<?php

namespace FS;

use Command\Command;
use Command\Create\Create;

class FS extends Command
{
  /**
   * @var Command[] $commands
   */
  private array $commands = [
    'create' => Create::class
  ];

  public static function getShortDescription(): string
  {
    return 'Manage fictional system images and commands.';
  }

  public static function getDescription(): array
  {
    return [static::getShortDescription()];
  }

  public static function getUsage(): array
  {
    return ['fs [command]'];
  }

  public static function getExamples(): array
  {
    return [];
  }

  public function call(): int
  {
    if (($this->argc == 0) || !key_exists($this->argv[0], $this->commands))
    {
      return $this->usage($this->commands);
    }
    else
    {
      /** @var Command $command */
      $command = new $this->commands[$this->argv[0]]($this->argc, $this->argv);
      return $command->call();
    }
  }
}
