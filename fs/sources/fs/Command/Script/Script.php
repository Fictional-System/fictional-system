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
      $component = json_decode(file_get_contents("$this->cwd/commands.cache"), true, 512, JSON_THROW_ON_ERROR);
    }
    catch (JsonException $ex)
    {
      throw new RuntimeException('Syntax error in `commands.cache`.', 0, $ex);
    }

    $this->completeScriptsName($component);
    $count = 0;
    foreach ($component as $componentNameAndTag => $commands)
    {
      [$componentFullName, $componentTag] = explode(':', $componentNameAndTag);
      foreach ($commands as $commandName => $command)
      {
        if (!$this->all)
        {
          $bypass = true;
          foreach ($this->argv as $cmd)
          {
            if (str_starts_with("$componentFullName/$commandName:$componentTag", $cmd))
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

        $this->generateCommandScript("$componentFullName/$commandName:$componentTag", $command);
        $count++;
      }
    }

    echo "$count scripts generated." . PHP_EOL;
  }

  private function completeScriptsName(array &$commandsConfig)
  {
    var_dump($commandsConfig);
    foreach ($commandsConfig as $name => $commands)
    {
      foreach ($commands as $command => $config)
      {

      }
    }


    $simplesCommand = [];
    $componentsCommand = [];
    $scriptnames = [];

    foreach ($commandsConfig as $name => $config)
    {
      [$longName, $tag] = explode(':', $name);
      [$domain, $component, $command] = explode('/', $longName);
      $simplesCommand[$command][] = "$domain/$component/$command:$tag";
      $componentsCommand["$component/$command"][] = "$domain/$component/$command:$tag";
    }

    foreach ($simplesCommand as $name => $commands)
    {
      if (count($commands) > 1)
      {
        foreach ($commands as $commandName)
        {
          [$longName, $tag] = explode(':', $commandName);
          [$domain, $component, $command] = explode('/', $longName);
          $scriptnames[$commandName] = $this->cleanName("$component/$command");
        }
      }
    }

    foreach ($componentsCommand as $name => $commands)
    {
      if (count($commands) > 1)
      {
        foreach ($commands as $commandName)
        {
          [$longName, $tag] = explode(':', $commandName);
          [$domain, $component, $command] = explode('/', $longName);
          $scriptnames[$commandName] = $this->cleanName("$domain/$component/$command");
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

  private function cleanVersion(string $tag): string
  {
    return preg_replace('/[^A-Za-z0-9]/', '_', $tag);
  }

  private function generateCommandScript(string $commandName, array $config): void
  {
    $cmdline = ['podman run --rm'];

    $this->getValue($config, 'interactive', false) ?? $cmdline[] = '-it';
    $this->getValue($config, 'detached', false) ?? $cmdline[] = '-d';
    $this->getValue($config, 'maths_ids', false) ?? $cmdline[] = '--userns=keep-id';
    $this->getValue($config, 'workdir', false) ?? $cmdline[] = '-w ' . $this->getValue($config, 'workdir');

    [$longName, $tag] = explode(':', $commandName);
    [$domain, $component, $command] = explode('/', $longName);
    $name = 'fs_' .
      $this->cleanName($longName) .
      '_' .
      $this->cleanVersion($tag);

    $cmdline[] = '--name ' . $name . '_$$';
    foreach ($this->getValue($config, 'volumes', []) as $volume)
    {
      if (count(explode(':', $volume)) !== 2)
      {
        throw new RuntimeException("Bad format in volumes definition for `$commandName`.");
      }

      $cmdline[] = "-v $volume:z";
    }
    $cmdline[] = $this->prefix . "/$domain/$component:$tag";
    $this->getValue($config, 'command', false) ?? $cmdline[] = $this->getValue($config, 'command');
    $cmdline[] = '$*';

    $scriptname = $this->getValue(
        $config,
        'scriptname',
        $this->cleanName($command)) .
      ($tag !== 'latest') ?? '_' . $this->cleanVersion($tag);
    $this->write(
      $scriptname,
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
      throw new RuntimeException("Unable to create `$name` script.");
    }
  }
}
