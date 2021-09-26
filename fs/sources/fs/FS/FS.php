<?php

namespace FS;

class FS
{
  public function __construct(private int $argc, private array $argv)
  {}

  public function run()
  {
    var_dump($this->argc, $this->argv);
  }
}
