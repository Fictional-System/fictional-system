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
    return [];
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
