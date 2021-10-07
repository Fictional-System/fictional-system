<?php

spl_autoload_register(function (string $classname) {
  $classname = str_replace('\\', '/', $classname);
  if (file_exists(__DIR__ . '/' . $classname . '.php'))
  {
    require_once(__DIR__ . '/' . $classname . '.php');
  }
});

exit((new FS\FS($argc, $argv))->run());
