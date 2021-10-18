<?php

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
  $tester->assertEqualStrict($cr->getOutputString(), '');
});
