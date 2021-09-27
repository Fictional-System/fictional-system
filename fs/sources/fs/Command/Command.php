<?php

namespace Command;

use FS\Usage;

abstract class Command extends Usage
{
  public function __construct(protected int $argc, protected array $argv)
  {
    $this->argc--;
    array_shift($this->argv);
  }

  public abstract function call(): int;
}
