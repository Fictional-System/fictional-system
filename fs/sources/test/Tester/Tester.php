<?php

namespace Tester;

class Tester
{
  /**
   * @var Test[]
   */
  private static array $tests = [];
  private int $lineSize = 10;

  public function __construct(private $dev = false)
  {
  }

  public static function it(string $name, callable $cb): void
  {
    self::$tests[] = new Test($name, $cb);
  }

  public static function clean(string $dir = '.')
  {
    foreach (scandir($dir) as $d)
    {
      if (in_array($d, ['.', '..']))
        continue;

      if (is_dir("$dir/$d"))
      {
        self::clean("$dir/$d");
        rmdir("$dir/$d");
      }
      else
      {
        unlink("$dir/$d");
      }
    }
  }

  private function loadDir(string $dir): void
  {
    foreach (scandir($dir) as $d)
    {
      if (in_array($d, ['.', '..']))
        continue;

      if (is_dir("$dir/$d"))
      {
        $this->loadDir("$dir/$d");
      }
      else
      {
        $parts = explode('.', $d);
        if (count($parts) && ($parts[count($parts) - 1] === 'php'))
        {
          (static function () use ($dir, $d) {
            require_once("$dir/$d");
          })();
        }
      }
    }
  }

  public function load(): Tester
  {
    $this->loadDir(__DIR__ . '/../Cases');
    return $this;
  }

  public function run(): int
  {
    $line = 0;
    $failedTests = [];

    echo 'Start ' . count(self::$tests) . ' tests :' . PHP_EOL;
    echo '    ';
    foreach (self::$tests as $test)
    {
      try
      {
        self::clean();
        $test->reset();
        $test->call($this->dev);
      }
      catch (\Exception $e)
      {
        $test->addFailure($e->getFile(), $e->getFile(), 'Unexpected exception : "' . $e->getMessage() . '"');
      }

      if (!$test->isFailed())
      {
        echo '.';
      }
      else
      {
        echo 'x';
        $failedTests[] = $test;
      }

      $line++;
      if ($line >= $this->lineSize)
      {
        echo PHP_EOL . '    ';
        $line = 0;
      }
    }

    echo PHP_EOL;

    if (count($failedTests) === 0)
    {
      echo 'All tests done.' . PHP_EOL;
      $this->debugOutput();
      return 0;
    }

    echo PHP_EOL . 'Failed tests :' . PHP_EOL;
    foreach ($failedTests as $test)
    {
      echo '  ' . $test->getName() . PHP_EOL;
      foreach ($test->getErrors() as $error)
      {
        echo '    ' . $error . PHP_EOL;
      }
    }

    $this->debugOutput();
    echo PHP_EOL;

    return 1;
  }

  private function debugOutput(): void
  {
    if ($this->dev)
    {
      echo PHP_EOL . 'Debug output :' . PHP_EOL;
      foreach (self::$tests as $test)
      {
        if (!strlen($test->getOutput()))
        {
          continue;
        }
        echo str_repeat('#', strlen($test->getName()) + 4) . PHP_EOL;
        echo '# ' . $test->getName() . ' #' . PHP_EOL;
        echo str_repeat('#', strlen($test->getName()) + 4) . PHP_EOL;
        echo $test->getOutput() . PHP_EOL;
      }
    }
  }
}
