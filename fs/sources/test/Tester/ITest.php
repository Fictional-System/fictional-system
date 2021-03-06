<?php

namespace Tester;

interface ITest
{
  public function shadowRun(...$args): void;

  public function assertFail(): void;

  public function assertTrue(mixed $value): void;

  public function assertFalse(mixed $value): void;

  public function assertEqual(mixed $val1, mixed $val2): void;

  public function assertEqualStrict(mixed $val1, mixed $val2): void;

  public function assertNotEqual(mixed $val1, mixed $val2): void;

  public function assertNotEqualStrict(mixed $val1, mixed $val2): void;

  public function assertFileNotExist(string $path): void;

  public function assertFileExist(string $path): void;

  public function assertDirExist(string $path): void;

  public function assertFileContent(string $path, string $content, bool $debugOutput = false): void;

  public function assertRun(string $args, int $return, string $output, bool $debugOutput = false): void;

  public function mkdir(string $dir): void;
}
