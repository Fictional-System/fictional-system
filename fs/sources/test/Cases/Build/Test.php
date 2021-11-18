<?php

use Samples\Config;
use Tester\ITest;
use Tester\Tester;

Tester::it('Nothing to build', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);

  $cr = $tester->run('build');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '0 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache', '');
});

Tester::it('Simple build', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $tester->assertEqualStrict($tester->run('enable foo/bar/test')->getReturn(), 0);

  $cr = $tester->run('build');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL);
});

Tester::it('Complete build', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $tester->assertEqualStrict($tester->run('enable foo/bar/test')->getReturn(), 0);

  $config = new Config('foo/bar/commands.json');
  $config['default']['arguments']['argument'] = 'value';
  $config->save();

  $cr = $tester->run('build');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'argument=argument:value' . PHP_EOL .
    'build' . PHP_EOL);
});
