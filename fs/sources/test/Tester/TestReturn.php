<?php

namespace Tester;

class TestReturn
{
  public function __construct(private array $output, private int $return)
  {
  }

  public function getOutput(): array
  {
    return $this->output;
  }

  public function getOutputString(): string
  {
    return implode(PHP_EOL, $this->output);
  }

  public function getReturn(): int
  {
    return $this->return;
  }

  public function dump(): void
  {
    var_dump([$this->return, $this->getOutputString()]);
  }
}
