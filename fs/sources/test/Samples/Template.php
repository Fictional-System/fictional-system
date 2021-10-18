<?php

namespace Samples;

use ArrayObject;
use RuntimeException;

class Template extends ArrayObject
{
  private array $template = [
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

  public function __construct(array $names = [], int $version = 1)
  {
    if (!key_exists($version, $this->template))
    {
      throw new RuntimeException("`$version` is not a valid version.");
    }

    $template = $this->template[$version]['base'];

    parent::__construct($template);

    foreach ($names as $name)
    {
      $this->addCommand($name, $name);
    }
  }

  public function toJson(): string
  {
    return json_encode($this->getArrayCopy(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  }

  public static function getTemplate(array $names = [], int $version = 1): Template
  {
    return (new self($names, $version));
  }

  public function addCommand(string $name, string $command, int $version = 1): Template
  {
    $template = $this->template[$version]['command'];
    $template['command'] = $command;
    $this['commands'][$name] = $template;

    return $this;
  }

  public function enableCommands(array $names): Template
  {
    foreach ($names as $name)
    {
      $this['commands'][$name]['enabled'] = true;
    }

    return $this;
  }

  public function enableCommand(string $name): Template
  {
    $this['commands'][$name]['enabled'] = true;

    return $this;
  }

  public function disableCommand(string $name): Template
  {
    $this['commands'][$name]['enabled'] = false;

    return $this;
  }

  public static function getJsonTemplate(array $names = [], int $version = 1): string
  {
    return (new self($names, $version))->toJson();
  }
}
