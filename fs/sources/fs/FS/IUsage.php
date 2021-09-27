<?php

namespace FS;

interface IUsage
{
  public static function getShortDescription(): string;

  /** @return string[] */
  public static function getDescription(): array;

  /** @return string[] */
  public static function getUsage(): array;

  /** @return string[] */
  public static function getExamples(): array;
}
