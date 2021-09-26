<?php

spl_autoload_register(function ($classname) {
  $classname = str_replace('\\', '/', $classname);
  if (file_exists(__DIR__ . "/${classname}.php"))
  {
    require_once(__DIR__ . "/${classname}.php");
  }
});

$fs = new FS\FS($argc, $argv);
$fs->run();
