<?php

namespace Tester;

class Test
{
  private array $errors = [];

  public function __construct(private string $name, private $cb)
  {
  }

  public function reset(): Test
  {
    $this->errors = [];
    return $this;
  }

  public function addFailure(string $file, string $line, string $message): void
  {
    $this->errors[] = ["$file:$line", $message];
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function isFailed(): bool
  {
    return count($this->errors) !== 0;
  }

  public function getErrors(): array
  {
    $length = max(array_map(function ($el) {
      return strlen($el[0]);
    }, $this->errors));
    return array_map(function ($el) use ($length) {
      return $el[0] . str_repeat(' ', $length - strlen($el[0]) + 1) . $el[1];
    }, $this->errors);
  }

  public function call(...$args): mixed
  {
    return call_user_func_array($this->cb, $args);
  }
}
