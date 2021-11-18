<?php

use Samples\Config;
use Tester\ITest;
use Tester\Tester;

Tester::it('Nothing to build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');

  $tester->assertRun('build', 0, '0 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache', '');
});

Tester::it('Simple build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL);
});

Tester::it('Complete build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $config = new Config('foo/bar/commands.json');
  $config['default']['arguments']['argument'] = 'value';
  $config->save();

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'argument=argument:value' . PHP_EOL .
    'build' . PHP_EOL);
});
