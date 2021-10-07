<?php

namespace Command\Create;

use Command\Command;
use RuntimeException;

class Create extends Command
{
  public static function getShortDescription(): string
  {
    return "Create a domain, component or command.";
  }

  public static function getDescription(): array
  {
    return [static::getShortDescription()];
  }

  public static function getUsage(): array
  {
    return ["fs create [domain[/component[/command]]]"];
  }

  public static function getExamples(): array
  {
    return [];
  }

  public function call(): void
  {
    if ($this->argc === 0)
    {
      $this->usage();
    }

    var_dump($this->argc, $this->argv);
  }
}
