<?php

namespace FS;

use Command\Command;

abstract class Usage implements IUsage
{
  /**
   * @param Command[] $tab
   */
  protected function usage(array $tab): int
  {
    $description = static::getDescription();
    if (count($description))
    {
      echo "Description:" . PHP_EOL;
      foreach ($description as $line)
      {
        echo "  $line" . PHP_EOL;
      }
      echo PHP_EOL;
    }

    $usage = static::getUsage();
    if (count($usage))
    {
      echo "Usage:" . PHP_EOL;
      foreach ($usage as $line)
      {
        echo "  $line" . PHP_EOL;
      }
      echo PHP_EOL;
    }

    $examples = static::getExamples();
    if (count($examples))
    {
      echo "Examples:" . PHP_EOL;
      foreach ($examples as $line)
      {
        echo "  $line" . PHP_EOL;
      }
      echo PHP_EOL;
    }

    if (count($tab))
    {
      $length = max(array_map("strlen", array_keys($tab)));
      echo "Available commands:" . PHP_EOL;
      foreach ($tab as $name => $class)
      {
        echo "  $name" . str_repeat(" ", $length - strlen($name) + 1) . $class::getShortDescription() . PHP_EOL;
      }
      echo PHP_EOL;
    }

    return 1;
  }
}
