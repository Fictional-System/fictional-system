<?php

namespace Samples;

class Script
{
  private string $prefix = 'localhost/fs';
  private string $workdir = '';
  private array $volumes = [];
  private bool $interactive = false;
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
    $this->volumes[] = '-v ' . $volume . ':z';

    return $this;
  }

  public function setWorkdir(string $workdir): Script
  {
    $this->workdir = $workdir;

    return $this;
  }

  public function getScript(): string
  {
    $name = 'fs_' .
      preg_replace('/[^A-Za-z0-9.]/', '_', $this->name) .
      '_' .
      preg_replace('/[^A-Za-z0-9]/', '_', $this->version);
    return '#!/bin/sh' . PHP_EOL .
      PHP_EOL .
      'podman run --rm ' .
      ($this->interactive ? '-it ' : '') .
      ($this->detached ? '-d ' : '') .
      ($this->maths_ids ? '--userns=keep-id ' : '') .
      ($this->workdir !== '' ? '-w ' . $this->workdir . ' ' : '') .
      '--name ' . $name . '_$$ '
      . implode(' ', $this->volumes) . (count($this->volumes) ? ' ' : '') .
      $this->prefix . '/' . $this->name . ':' . $this->version . ' ' .
      $this->command . ($this->detached ? '' : ' $*') . PHP_EOL;
  }
}
