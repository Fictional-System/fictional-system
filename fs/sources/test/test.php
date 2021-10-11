<?php

use Tester\Tester;

spl_autoload_register(function (string $classname) {
  $classname = str_replace('\\', '/', $classname);

  $parts = explode('/', $classname);
  $classname = match ($parts[0])
  {
    'FS', 'Command' => "../fs/$classname",
    default => $classname,
  };

  if (file_exists(__DIR__ . '/' . $classname . '.php'))
  {
    require_once(__DIR__ . '/' . $classname . '.php');
  }
});

if ($argc > 1 && ($argv[1] === 'dev'))
{
  exit((new Tester(true))->load()->run());
}
else
{
  exit((new Tester())->load()->run());
}
