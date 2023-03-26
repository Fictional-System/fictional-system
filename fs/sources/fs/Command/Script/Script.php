<?php

namespace Command\Script;

use Command\Command;
use Command\Config;
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
    return ['fs script [domain[/component[/command[:version]]]]'];
  }

  public static function getExamples(): array
  {
    return [
      'fs script all',
      'fs script foo',
      'fs script foo/bar',
      'fs script foo/bar/test',
      'fs script foo/bar/test:latest',
      'fs script foo/bar/test bar/foo/test',
      'fs script foo/bar/foo foo/bar/bar',
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
    $this->fw->mkdir('bin');
    $this->fw->cleanDir('bin', ['fs']);
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

  private function completeScriptsName(array &$components)
  {
    $simplesCommand = [];
    $componentsCommand = [];
    $scriptnames = [];

    foreach ($components as $componentNameAndTag => $commands)
    {
      [$componentFullName, $componentTag] = explode(':', $componentNameAndTag);
      [$domainName, $componentName] = explode('/', $componentFullName);
      foreach ($commands as $commandName => $command)
      {
        $simplesCommand["$commandName:$componentTag"][] = "$domainName/$componentName/$commandName:$componentTag";
        $componentsCommand["$componentName/$commandName:$componentTag"][] = "$domainName/$componentName/$commandName:$componentTag";
      }
    }

    foreach ($simplesCommand as $commands)
    {
      if (count($commands) > 1)
      {
        foreach ($commands as $fullName)
        {
          [$commandFullName, $tag] = explode(':', $fullName);
          [$domainName, $componentName, $commandName] = explode('/', $commandFullName);
          $scriptnames["$domainName/$componentName:$tag"][$commandName] = Config::cleanName("$componentName/$commandName");
        }
      }
    }

    foreach ($componentsCommand as $commands)
    {
      if (count($commands) > 1)
      {
        foreach ($commands as $fullName)
        {
          [$commandFullName, $tag] = explode(':', $fullName);
          [$domainName, $componentName, $commandName] = explode('/', $commandFullName);
          $scriptnames["$domainName/$componentName:$tag"][$commandName] = Config::cleanName("$domainName/$componentName/$commandName");
        }
      }
    }

    foreach ($scriptnames as $componentNameAndTag => $commands)
    {
      foreach ($commands as $commandName => $scriptname)
      {
        $components[$componentNameAndTag][$commandName]['scriptname'] = $scriptname;
      }
    }
  }

  private function generateCommandScript(string $commandName, array $config): void
  {
    $cmdline = ['podman run --rm'];

    !($this->getValue($config, 'interactive', false) && $this->getValue($config, 'init', true)) ?: $cmdline[] = '--init';
    !$this->getValue($config, 'interactive', false) ?: $cmdline[] = '-it';
    !$this->getValue($config, 'detached', false) ?: $cmdline[] = '-d';
    !$this->getValue($config, 'match_ids', false) ?: $cmdline[] = '--userns=keep-id';
    !$this->getValue($config, 'workdir', false) ?: $cmdline[] = '-w ' . $this->getValue($config, 'workdir');

    [$longName, $tag] = explode(':', $commandName);
    [$domain, $component, $command] = explode('/', $longName);
    $name = 'fs_' .
      Config::cleanName($longName) .
      '_' .
      Config::cleanTag($tag);

    $cmdline[] = '--name ' . $name . '_$$';
    !$this->fw->fileExists("$domain/$component/cache/$command.env") ?: $cmdline[] = '--env-file "$base/' . "$domain/$component/cache/$command.env\"";
    foreach ($this->getValue($config, 'ports', []) as $port)
    {
      switch (count(explode(':', $port)))
      {
        case 2:
        case 3:
          break;
        default:
          throw new RuntimeException("Bad format in ports definition for `$commandName`.");
      }

      $cmdline[] = "-p $port";
    }

    $dirsToCreate = [];
    $dirsToCreateString = '';
    foreach ($this->getValue($config, 'volumes', []) as $volume)
    {
      switch (count(explode(':', $volume)))
      {
        case 2:
        case 3:
          break;
        default:
          throw new RuntimeException("Bad format in volumes definition for `$commandName`.");
      }
      $dirsToCreate[] = explode(':', $volume)[0];

      $cmdline[] = "-v $volume";
    }
    !count($dirsToCreate) ?: $dirsToCreateString = "mkdir -p " . implode(' ', $dirsToCreate) . PHP_EOL;
    $networksToCreate = [];
    $networksToCreateString = '';
    foreach ($this->getValue($config, 'networks', []) as $network)
    {
      $networksToCreate[] = $network;
      $cmdline[] = "--network $network";
    }
    !count($networksToCreate) ?: $networksToCreateString = implode(PHP_EOL, array_map(function ($networkToCreate) {
      return "podman network create --ignore " . $networkToCreate;
    }, $networksToCreate));

    $cmdline[] = $this->prefix . "/$domain/$component:$tag";
    !$this->getValue($config, 'command', false) ?: $cmdline[] = $this->getValue($config, 'command');
    $cmdline[] = '$*';

    $scriptname = $this->getValue(
        $config,
        'scriptname',
        Config::cleanName($command)) .
      (($tag === 'latest') ? '' : '_' . Config::cleanTag($tag));
    $this->write(
      $scriptname,
      '#!/bin/sh' . PHP_EOL . PHP_EOL .
      'base=$(dirname $(dirname "$0"))' . PHP_EOL . PHP_EOL .
      $dirsToCreateString . $networksToCreateString . PHP_EOL . PHP_EOL .
      implode(' ', $cmdline) . PHP_EOL);
  }

  private function getValue($config, $key, $default = ''): mixed
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
