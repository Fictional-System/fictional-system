<?php

namespace Tester;

interface ITester
{
  public function call(...$args): TestReturn;

  public function assertFail(): void;

  public function assertTrue(mixed $value): void;

  public function assertFalse(mixed $value): void;

  public function assertEqual(mixed $val1, mixed $val2): void;

  public function assertEqualStrict(mixed $val1, mixed $val2): void;

  public function assertNotEqual(mixed $val1, mixed $val2): void;

  public function assertNotEqualStrict(mixed $val1, mixed $val2): void;

  public function assertFileExist(string $path): void;

  public function assertDirExist(string $path): void;

  public function assertFileContent(string $path, string $content): void;
}
