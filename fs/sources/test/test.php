<?php

use Tester\Tester;

spl_autoload_register(function (string $classname) {
  $classname = str_replace('\\', '/', $classname);
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
