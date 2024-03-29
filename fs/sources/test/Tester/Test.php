<?php

namespace Tester;

use RuntimeException;

class Test implements ITest
{
  private array $errors = [];
  private string $output = '';
  private bool $dev = false;

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

  public function getOutput(): string
  {
    return $this->output;
  }

  public function call(bool $dev = false): mixed
  {
    $this->dev = $dev;
    ob_start();
    $ret = call_user_func_array($this->cb, [$this]);
    $this->output = ob_get_clean();
    return $ret;
  }

  private function run(...$args): TestReturn
  {
    exec(($this->dev ? 'php /fs/fs/run.php ' : 'php /usr/local/bin/fs.phar ') . implode(' ', $args) . ' 2>&1', $output, $return);

    return new TestReturn($output, $return);
  }

  public function shadowRun(...$args): void
  {
    if ($this->run(...$args)->getReturn() !== 0)
    {
      $this->addError();
    }
  }

  private function addError(): void
  {
    $debug = debug_backtrace();
    $this->addFailure($debug[1]['file'], $debug[1]['line'], $debug[1]['function']);
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

  public function assertFileNotExist(string $path): void
  {
    if (file_exists($path))
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

  public function assertFileContent(string $path, string $content, bool $debugOutput = false): void
  {
    if ($debugOutput)
    {
      var_dump([
        'expected' => $content,
        $path => file_get_contents($path),
      ]);
    }

    if (!file_exists($path) || is_dir($path) || (file_get_contents($path) !== $content))
    {
      $this->addError();
    }
  }

  public function assertRun(string $args, int $return, string $output, bool $debugOutput = false): void
  {
    $cr = $this->run($args);

    if ($debugOutput)
    {
      $cr->dump();
    }

    if ($cr->getReturn() !== $return)
    {
      $this->addError();
      return;
    }

    if ($cr->getOutputString() !== $output)
    {
      $this->addError();
    }
  }

  public function mkdir(string $dir): void
  {
    if (@mkdir($dir, 0700, true) === false)
    {
      throw new RuntimeException("Unable to create `$dir`.");
    }
  }
}
