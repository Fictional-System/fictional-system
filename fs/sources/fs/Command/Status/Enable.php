<?php

namespace Command\Status;

class Enable extends SwitchStatus
{
  public static function getShortDescription(): string
  {
    return "Enable a domain, component or command.";
  }

  public static function getDescription(): array
  {
    return [static::getShortDescription()];
  }

  public static function getUsage(): array
  {
    return ["fs enable [domain[/component[/command]]]"];
  }

  public static function getExamples(): array
  {
    return [
      "fs enable all",
      "fs enable foo",
      "fs enable foo/bar",
      "fs enable foo/bar/test",
      "fs enable foo/bar/test bar/foo/test",
      "fs enable foo/bar/foo foo/bar/bar",
    ];
  }

  public function call(): void
  {
    if ($this->argc === 0)
    {
      $this->usage();
    }

    foreach ($this->argv as $name)
    {
      call_user_func_array([$this, 'switch'], array_merge([true], explode('/', $name)));
    }
  }
}
