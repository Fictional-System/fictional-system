<?php

namespace Tester;

class Tester implements ITester
{
  /**
   * @var Test[]
   */
  private static array $tests = [];
  private int $lineSize = 10;
  private Test $currentTest;

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
          require_once("$dir/$d");
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
        $this->currentTest = $test->reset();
        if (!$this->dev)
          ob_start();
        $test->call($this);
        if (!$this->dev)
          ob_end_clean();
      }
      catch (\Exception $e)
      {
        $this->currentTest->addFailure($e->getFile(), $e->getFile(), 'Unexpected exception : "' . $e->getMessage() . '"');
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

    echo PHP_EOL;

    return 1;
  }

  public function call(...$args): TestReturn
  {
    exec(($this->dev ? 'php /fs/fs/run.php ' : 'php /usr/local/bin/fs.phar ') . implode(' ', $args) . ' 2>&1', $output, $return);

    return new TestReturn($output, $return);
  }

  private function addError(): void
  {
    $debug = debug_backtrace();
    $this->currentTest->addFailure($debug[1]['file'], $debug[1]['line'], $debug[1]['function']);
  }

  public function assertFail(): void
  {
    $this->addError();
  }

  public function assertTrue(mixed $value): void
  {
    if (!$value)
    {
      $this->addError();
    }
  }

  public function assertFalse(mixed $value): void
  {
    if ($value)
    {
      $this->addError();
    }
  }

  public function assertEqual(mixed $val1, mixed $val2): void
  {
    if ($val1 != $val2)
    {
      $this->addError();
    }
  }

  public function assertEqualStrict(mixed $val1, mixed $val2): void
  {
    if ($val1 !== $val2)
    {
      $this->addError();
    }
  }

  public function assertNotEqual(mixed $val1, mixed $val2): void
  {
    if ($val1 == $val2)
    {
      $this->addError();
    }
  }

  public function assertNotEqualStrict(mixed $val1, mixed $val2): void
  {
    if ($val1 === $val2)
    {
      $this->addError();
    }
  }

  public function assertFileExist(string $path): void
  {
    if (!file_exists($path))
    {
      $this->addError();
    }
  }

  public function assertDirExist(string $path): void
  {
    if (!file_exists($path) || !is_dir($path))
    {
      $this->addError();
    }
  }

  public function assertFileContent(string $path, string $content): void
  {
    if (!file_exists($path) || is_dir($path) || (file_get_contents($path) !== $content))
    {
      $this->addError();
    }
  }
}
