<?php

namespace Tester;

class Test
{
  public function __construct(private string $name, private $cb)
  {
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function call(...$args): bool
  {
    return call_user_func_array($this->cb, $args);
  }
}
