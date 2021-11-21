<?php

namespace Command\Script;

use Command\Command;
use JsonException;
use RuntimeException;

class Script extends Command
{
  private bool $all = false;
  private string $prefix = 'localhost/fs';

  public static function getShortDescription(): string
  {
    return 'Generate scripts for enabled commands.';
  }

  public static function getDescription(): array
  {
    return [static::getShortDescription()];
  }

  public static function getUsage(): array
  {
    return ['fs enable [domain[/component[/command[:version]]]]'];
  }

  public static function getExamples(): array
  {
    return [
      'fs script all',
      'fs enable foo',
      'fs enable foo/bar',
      'fs enable foo/bar/test',
      'fs enable foo/bar/test:latest',
      'fs enable foo/bar/test bar/foo/test',
      'fs enable foo/bar/foo foo/bar/bar',
    ];
  }

  public function call(): void
  {
    if ($this->argc === 0)
    {
      $this->usage();
    }

    if (!file_exists("$this->cwd/commands.cache"))
    {
      throw new RuntimeException('Command cache not found. Run `fs build` before.');
    }

    if (in_array('all', $this->argv))
    {
      $this->all = true;
    }

    $this->parseCommandFile();
  }

  private function parseCommandFile(): void
  {
    try
    {
      $commands = json_decode(file_get_contents("$this->cwd/commands.cache"), true, 512, JSON_THROW_ON_ERROR);
    }
    catch (JsonException $ex)
    {
      throw new RuntimeException('Syntax error in `commands.cache`.', 0, $ex);
    }

    $this->completeScriptsName($commands);
    $count = 0;
    foreach ($commands as $name => $config)
    {
      if (!$this->all)
      {
        $bypass = true;
        foreach ($this->argv as $cmd)
        {
          if (str_starts_with($name, $cmd))
          {
            $bypass = false;
            break;
          }
        }

        if ($bypass)
        {
          continue;
        }
      }

      $this->generateCommandScript($name, $config);
      $count++;
    }

    echo "$count scripts generated." . PHP_EOL;
  }

  private function completeScriptsName(array &$commandsConfig)
  {
    $simplesCommand = [];
    $componentsCommand = [];
    $scriptnames = [];

    foreach ($commandsConfig as $name => $config)
    {
      [$domain, $component, $command] = explode('/', explode(':', $name)[0]);
      $simplesCommand[$command][] = "$domain/$component/$command";
      $componentsCommand["$component/$command"][] = "$domain/$component/$command";
    }

    foreach ($simplesCommand as $name => $commands)
    {
      if (count($commands) > 1)
      {
        foreach ($commands as $command)
        {
          [$domain, $component, $command] = explode('/', $command);
          $scriptnames[$command] = $this->cleanName("$component/$command");
        }
      }
    }

    foreach ($componentsCommand as $name => $commands)
    {
      if (count($commands) > 1)
      {
        foreach ($commands as $command)
        {
          [$domain, $component, $command] = explode('/', $command);
          $scriptnames[$command] = $this->cleanName("$domain/$component/$command");
        }
      }
    }

    foreach ($scriptnames as $name => $scriptname)
    {
      $commandsConfig[$name]['scriptname'] = $scriptname;
    }
  }

  private function cleanName(string $name): string
  {
    return preg_replace('/[^A-Za-z0-9.]/', '_', $name);
  }

  private function cleanVersion(string $version): string
  {
    return preg_replace('/[^A-Za-z0-9]/', '_', $version);
  }

  private function generateCommandScript(string $command, array $config): void
  {
    $cmdline = ['podman run --rm'];

    $this->getValue($config, 'interactive', false) ?? $cmdline[] = '-it';
    $this->getValue($config, 'detached', false) ?? $cmdline[] = '-d';
    $this->getValue($config, 'maths_ids', false) ?? $cmdline[] = '--userns=keep-id';
    $this->getValue($config, 'workdir', '') ?? $cmdline[] = '-w ' . $this->getValue($config, 'workdir', '');

    [$longName, $version] = explode(':', $command);
    $name = 'fs_' .
      $this->cleanName($longName) .
      '_' .
      $this->cleanVersion($version);

    $cmdline[] = '--name ' . $name . '_$$';
    foreach ($this->getValue($config, 'volumes', []) as $volume)
    {
      if (count(explode(':', $volume)) !== 2)
      {
        throw new RuntimeException("Bad format in volumes definition for `$command`.");
      }

      $cmdline[] = "-v $volume:z";
    }
    $cmdline[] = $this->prefix . "/$longName:$version";
    $cmdline[] = $this->getValue($config, 'command', '');
    $cmdline[] = '$*';

    $this->write(
      $this->getValue(
        $config,
        'scriptnames',
        $this->cleanName(explode('/', $longName)[2])) .
      ($version !== 'latest') ?? '_' . $this->cleanVersion($version),
      '#!/bin/sh' . PHP_EOL . PHP_EOL . implode(' ', $cmdline) . PHP_EOL);
  }

  private function getValue(&$config, $key, $default = ''): mixed
  {
    if (key_exists($key, $config))
    {
      return $config[$key];
    }

    return $default;
  }

  private function write(string $name, string $content): void
  {
    if (@file_put_contents("$this->cwd/bin/$name", $content, LOCK_EX) === false)
    {
      throw new RuntimeException("Unable to create $name script.");
    }
  }
}
