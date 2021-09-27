<?php

namespace Command;

use FS\IUsage;

abstract class Command implements IUsage
{
  public function __construct(protected int $argc, protected array $argv)
  {
    $this->argc--;
    array_shift($this->argv);
  }

  /**
   * @param Command[] $tab
   */
  protected function usage(array $tab): int
  {
    $this->displayPart('Description', static::getDescription());
    $this->displayPart('Usage', static::getUsage());
    $this->displayPart('Examples', static::getExamples());

    if (count($tab))
    {
      $length = max(array_map('strlen', array_keys($tab)));
      echo 'Available commands:' . PHP_EOL;
      foreach ($tab as $name => $class)
      {
        echo '  ' . $name . str_repeat(' ', $length - strlen($name) + 1) . $class::getShortDescription() . PHP_EOL;
      }
      echo PHP_EOL;
    }

    return 1;
  }

  private function displayPart(string $name, array $part)
  {
    if (count($part))
    {
      echo $name . ':' . PHP_EOL;
      foreach ($part as $line)
      {
        echo '  ' . $line . PHP_EOL;
      }
      echo PHP_EOL;
    }
  }

  public abstract function call(): int;
}
