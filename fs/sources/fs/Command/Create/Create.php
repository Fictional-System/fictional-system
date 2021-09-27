<?php

namespace Command\Create;

use Command\Command;

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
    return ["fs create [domain]"];
  }

  public static function getExamples(): array
  {
    return [];
  }

  public function call(): int
  {
    var_dump($this->argc, $this->argv);
    return 1;
  }
}
