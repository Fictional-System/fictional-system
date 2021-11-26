<?php

namespace Command;

use Exception;
use FS\IUsage;
use RuntimeException;

abstract class Command implements IUsage
{
  protected string $cwd;
  protected FileWrapper $fw;

  public function __construct(protected int $argc, protected array $argv)
  {
    $this->argc--;
    array_shift($this->argv);
    $this->cwd = getcwd();
    $this->fw = new FileWrapper(getcwd());
  }

  /**
   * @throws Exception
   */
  public static function callCommand(string $name, array $args, bool $quiet = false): void
  {
    if (!is_subclass_of($name, Command::class))
    {
      throw new RuntimeException("`$name` is not a valid command.");
    }

    if ($quiet)
    {
      try
      {
        ob_start();
        (new $name(count($args) + 1, array_merge([$name], $args)))->call();
      }
      catch (Exception $ex)
      {
        ob_end_clean();
        throw $ex;
      }
      ob_end_clean();
    }
    else
    {
      (new $name(count($args) + 1, array_merge([$name], $args)))->call();
    }
  }

  protected function displayError(string $message): int
  {
    file_put_contents('php://stderr', $message);
    return 1;
  }

  protected function displayException(Exception $ex): int
  {
    return $this->displayError($ex->getMessage());
  }

  /**
   * @param Command[] $tab
   */
  protected function usage(array $tab = []): void
  {
    ob_start();
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
    throw new \RuntimeException(ob_get_clean());
  }

  /**
   * @param string $name
   * @param string[] $part
   */
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

  public abstract function call(): void;
}
