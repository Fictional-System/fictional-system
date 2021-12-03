<?php

namespace Samples;

use ArrayObject;
use JsonException;
use RuntimeException;

class Config extends ArrayObject
{
  /**
   * @throws JsonException
   */
  public function __construct(private string $path)
  {
    if (!file_exists($path))
    {
      throw new RuntimeException("File `$path` does not exist.");
    }

    if (is_dir($path))
    {
      throw new RuntimeException("`$path` is a directory.");
    }

    if (($content = file_get_contents($path)) === false)
    {
      throw new RuntimeException("Cannot open `$path`.");
    }

    parent::__construct(json_decode($content, true, 512, JSON_THROW_ON_ERROR));
  }

  public function save(): void
  {
    if (($config = json_encode($this->getArrayCopy(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false)
    {
      throw new RuntimeException('Config encode error.');
    }

    if (file_put_contents($this->path, $config . PHP_EOL) === false)
    {
      throw new RuntimeException("Cannot write `$this->path`.");
    }
  }
}
