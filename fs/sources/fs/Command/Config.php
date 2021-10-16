<?php

namespace Command;

use ArrayObject;
use JsonException;
use RuntimeException;

class Config extends ArrayObject
{
  private static array $template = [
    '#command#' => [
      'main' => [
        'command' => '#command#',
        'enabled' => false,
        'versions' => ['latest'],
        'from' => [],
      ],
      'options' => [
        'volumes' => ['$PWD:/app'],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match-ids' => false,
        'workdir' => '/app'
      ],
      'arguments' => [],
      'env' => [],
    ]
  ];

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
    self::write($this->path, $this->getArrayCopy());
  }

  public function merge(array $array): Config
  {
    $this->exchangeArray(array_merge($this->getArrayCopy(), $array));

    return $this;
  }

  public function enable(string $name): Config
  {
    return $this->switchStatus($name, true);
  }

  public function disable(string $name): Config
  {
    return $this->switchStatus($name, false);
  }

  public function switchStatus(string $name, bool $status): Config
  {
    $this[$name]['main']['enabled'] = $status;
    return $this;
  }

  private static function write(string $path, array $content): void
  {
    if (($config = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false)
    {
      throw new RuntimeException("Config encode error.");
    }

    if (file_put_contents($path, $config) === false)
    {
      throw new RuntimeException("Cannot write `$path`.");
    }
  }

  /**
   * @param string[] $replacements
   * @return array
   */
  public static function getTemplate(array $replacements): array
  {
    $template = json_encode(self::$template);
    foreach ($replacements as $key => $value)
    {
      $template = str_replace("#$key#", $value, $template);
    }

    return json_decode($template, true);
  }

  public static function createTemplate(string $path, array $replacements = []): void
  {
    self::write($path, self::getTemplate(array_merge(['command' => 'default'], $replacements)));
  }
}
