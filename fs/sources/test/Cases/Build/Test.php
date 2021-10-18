<?php

use Samples\Script;
use Tester\ITest;
use Tester\Tester;

Tester::it('Nothing to build', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);

  $cr = $tester->run('build');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '');
});

Tester::it('Simple build', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $tester->assertEqualStrict($tester->run('enable foo/bar/test')->getReturn(), 0);

  $cr = $tester->run('build');
  $cr->dump();
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'foo/bar/test:latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL
  );
  $testCommand = Script::get('foo/bar/test', 'latest', 'test')
    ->addVolume('$PWD:/app')
    ->setWorkdir('/app');
  // TODO Maybe in a second command ?
  $tester->assertFileContent('bin/test', $testCommand->getScript());
  $tester->assertFileContent('bin/test_latest', $testCommand->getScript());
});
