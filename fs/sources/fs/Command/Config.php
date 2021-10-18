<?php

namespace Command;

use ArrayObject;
use JsonException;
use RuntimeException;

class Config extends ArrayObject
{
  private static int $version = 1;

  private static array $template = [
    1 => [
      'base' => [
        'version' => 1,
        'default' => [
          'versions' => [
            'latest' => [
              'arguments' => [],
              'env' => [],
              'from' => [],
            ],
          ],
          'arguments' => [],
          'env' => [],
          'volumes' => ['$PWD:/app'],
          'ports' => [],
          'interactive' => false,
          'detached' => false,
          'match-ids' => false,
          'workdir' => '/app'
        ],
        'commands' => [],
      ],
      'command' => [
        'command' => '#command#',
        'enabled' => false,
      ],
    ],
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

    if (self::$version > $this['version'])
    {
      // Migrate configuration;
    }
  }

  public function save(): void
  {
    self::write($this->path, $this->getArrayCopy());
  }

  public function setCommand(string $name, array $data): Config
  {
    $this['commands'][$name] = $data;
    return $this;
  }

  public function createCommand(string $name): Config
  {
    return $this->setCommand($name, Config::getTemplate($name));
  }

  public function getCommand(string $name): array
  {
    if (!key_exists($name, $this['commands']))
    {
      throw new RuntimeException("Command `$name` does not exist.");
    }

    return $this['commands'][$name];
  }

  public function getCommandNames(): array
  {
    return array_keys($this['commands']);
  }

  public function getEnabledCommands(): array
  {
    $commands = [];

    foreach ($this['commands'] as $key => $value)
    {
      if ($value['enabled'])
      {
        $commands[] = $key;
      }
    }

    return $commands;
  }

  public function hasCommand(string $name): bool
  {
    return key_exists($name, $this['commands']);
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
    if (!key_exists($name, $this['commands']))
    {
      throw new RuntimeException("Command `$name` does not exist.");
    }

    $this['commands'][$name]['enabled'] = $status;
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

  public static function getTemplate(string $name): array
  {
    $template = self::$template[1]['command'];
    $template['command'] = $name;

    return $template;
  }

  public static function createTemplate(string $path): void
  {
    self::write($path, self::$template[self::$version]['base']);
  }
}
