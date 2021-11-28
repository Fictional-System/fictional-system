<?php

namespace Command;

use ArrayObject;
use JsonException;
use RuntimeException;

class Config extends ArrayObject
{
  const VERSION = 1;

  const TEMPLATE = [
    1 => [
      'base' => [
        'version' => 1,
        'default' => [
          'from' => [],
          'tags' => [
            'latest' => [
              'arguments' => [
                'FROM_TAG' => 'latest',
              ],
            ],
          ],
          'arguments' => [
            'FROM_TAG' => 'latest',
          ],
          'volumes' => ['$PWD:/app'],
          'ports' => [],
          'interactive' => false,
          'detached' => false,
          'match_ids' => false,
          'workdir' => '/app',
        ],
        'commands' => [],
      ],
      'command' => [
        'command' => '#command#',
        'enabled' => false,
      ],
      'default' => [
        'volumes' => [],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '',
      ],
    ],
  ];

  const FILTER_CONFIG_KEYS = [
    'default' => [
      'from',
      'arguments',
      'volumes',
      'ports',
      'interactive',
      'detached',
      'match_ids',
      'workdir',
    ],
    'tag' => [
      'arguments',
      'volumes',
      'ports',
      'interactive',
      'detached',
      'match_ids',
      'workdir',
    ],
    'command' => [
      'volumes',
      'ports',
      'interactive',
      'detached',
      'match_ids',
      'workdir',
      'command',
    ],
    'final' => [
      'from',
      'arguments',
      'volumes',
      'ports',
      'interactive',
      'detached',
      'match_ids',
      'workdir',
      'command',
    ],
    'cache' => [
      'volumes',
      'ports',
      'interactive',
      'detached',
      'match_ids',
      'workdir',
      'command',
    ],
    'build' => [
      'from',
      'arguments',
    ]
  ];

  const CONFIG_MERGE_KEYS_VALUE = [
    'arguments',
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

    if (self::VERSION > $this['version'])
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

    if (@file_put_contents($path, $config . PHP_EOL) === false)
    {
      throw new RuntimeException("Cannot write `$path`.");
    }
  }

  public static function getTemplate(string $name): array
  {
    $template = self::TEMPLATE[1]['command'];
    $template['command'] = $name;

    return $template;
  }

  public static function createTemplate(string $path): void
  {
    self::write($path, self::TEMPLATE[self::VERSION]['base']);
  }

  /**
   * @return string[]
   */
  public function getTags(): array
  {
    return array_keys($this['default']['tags']);
  }

  public function getBuildConfig(string $tag = 'latest'): array
  {
    return $this->filterConfig(
      $this->mergeConfig(
        $this->filterConfig($this['default']),
        $this->filterConfig($this['default']['tags'][$tag], 'tag')
      ),
      'build');
  }

  public function getTagConfig(string $cmd, string $tag): array
  {
    if (!in_array($tag, $this->getTags($cmd)))
    {
      throw new RuntimeException("Version `$tag` not found for command `$cmd`.");
    }

    $config = $this->filterConfig($this['default']);
    $commandConfig = $this->filterConfig($this['commands'][$cmd], 'command');
    $tagConfig = $this->filterConfig($this['default']['tags'][$tag], 'tag');
    $commandTagConfig = [];
    if (key_exists('tags', $this['commands'][$cmd]) && key_exists($tag, $this['commands'][$cmd]['tags']))
    {
      $commandTagConfig = $this->filterConfig($this['commands'][$cmd]['tags'][$tag], 'command');
    }

    $config = $this->mergeConfig($config, $commandConfig);
    $config = $this->mergeConfig($config, $tagConfig);
    $config = $this->mergeConfig($config, $commandTagConfig);

    return $this->filterConfig($config, 'command', $this['version']);
  }

  private function filterConfig(array $arr, string $keysFilter = 'default', int $version = null): array
  {
    if (!key_exists($keysFilter, self::FILTER_CONFIG_KEYS))
    {
      throw new RuntimeException("Config keys filter `$keysFilter` does not exist.");
    }

    $arr = array_filter($arr, function ($key) use ($keysFilter) {
      return in_array($key, self::FILTER_CONFIG_KEYS[$keysFilter]);
    }, ARRAY_FILTER_USE_KEY);

    if ($version !== null)
    {
      return array_merge(self::TEMPLATE[$version]['default'], $arr);
    }

    return $arr;
  }

  private function mergeConfig(array $arr, array $mergeArr): array
  {
    foreach ($mergeArr as $key => $fields)
    {
      if (in_array($key, self::CONFIG_MERGE_KEYS_VALUE))
      {
        foreach ($fields as $k => $v)
        {
          $arr[$key][$k] = $v;
        }
      }
      else if (is_array($fields))
      {
        if (!key_exists($key, $arr))
        {
          $arr[$key] = $fields;
        }
        else
        {
          $arr[$key] = array_merge($arr[$key], $fields);
        }
      }
      else
      {
        $arr[$key] = $fields;
      }
    }

    return $arr;
  }
}
