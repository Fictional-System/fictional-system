<?php

namespace Samples;

class Script
{
  private string $prefix = 'localhost/fs';
  private string $workdir = '/app';
  private array $volumes = [];
  private array $networks = [];
  private array $ports = [];
  private array $envs = [];
  private bool $interactive = false;
  private bool $init = false;
  private bool $maths_ids = false;
  private bool $detached = false;

  public function __construct(private string $name, private string $version, private string $command)
  {
  }

  public static function get(string $name, string $version, string $command): Script
  {
    return new Script($name, $version, $command);
  }

  public function addVolume(string $volume): Script
  {
    $this->volumes[] = $volume;

    return $this;
  }

  public function addNetwork(string $network): Script
  {
    $this->networks[] = $network;

    return $this;
  }

  public function addPorts(string $port): Script
  {
    $this->ports[] = $port;

    return $this;
  }

  public function setInteractive(bool $interactive): Script
  {
    $this->interactive = $interactive;

    return $this;
  }

  public function setInit(bool $init): Script
  {
    $this->init = $init;

    return $this;
  }

  public function setMatchIds(bool $maths_ids): Script
  {
    $this->maths_ids = $maths_ids;

    return $this;
  }

  public function setDetached(bool $detached): Script
  {
    $this->detached = $detached;

    return $this;
  }

  public function setWorkdir(string $workdir): Script
  {
    $this->workdir = $workdir;

    return $this;
  }

  public function addEnvFile(string $file): Script
  {
    $this->envs[] = $file;

    return $this;
  }

  public function getScript(): string
  {
    [$domain, $component] = explode('/', $this->name);
    $imageName = $this->prefix . "/$domain/$component:" . $this->version;

    $name = 'fs_' .
      preg_replace('/[^A-Za-z0-9.]/', '_', $this->name) .
      '_' .
      preg_replace('/[^A-Za-z0-9]/', '_', $this->version);

    $cmdline = ['podman run --rm'];
    !$this->init ?: $cmdline[] = '--init';
    !$this->interactive ?: $cmdline[] = '-it';
    !$this->detached ?: $cmdline[] = '-d';
    !$this->maths_ids ?: $cmdline[] = '--userns=keep-id';
    $this->workdir == '' ?: $cmdline[] = '-w ' . $this->workdir;
    $cmdline[] = '--name ' . $name . '_$$';
    foreach ($this->envs as $env)
    {
      $cmdline[] = "--env-file \"\$base/$env\"";
    }
    foreach ($this->ports as $port)
    {
      $cmdline[] = "-p $port";
    }
    foreach ($this->volumes as $volume)
    {
      $cmdline[] = "-v $volume";
    }
    foreach ($this->networks as $network)
    {
      $cmdline[] = "--network $network";
    }
    $cmdline[] = $imageName;
    !$this->command ?: $cmdline[] = $this->command;
    $cmdline[] = '$*';

    $dirsToCreateString = '';
    if (count($this->volumes))
    {
      $dirsToCreateString = "mkdir -p " . implode(' ', array_map(function ($volume) {
          return explode(':', $volume)[0];
        }, $this->volumes)) . PHP_EOL;
    }
    $networksToCreateString = '';
    if (count($this->networks))
    {
      $networksToCreateString = implode( PHP_EOL, array_map(function ($network) {
        return "podman network create --ignore " . $network;
      }, $this->networks));
    }


    return '#!/bin/sh' . PHP_EOL . PHP_EOL .
      'base=$(dirname $(dirname "$0"))' . PHP_EOL . PHP_EOL .
      $dirsToCreateString . $networksToCreateString . PHP_EOL . PHP_EOL .
      implode(' ', $cmdline) . PHP_EOL;
  }
}
