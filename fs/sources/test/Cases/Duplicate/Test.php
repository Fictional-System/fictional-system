<?php

use Tester\ITest;
use Tester\Tester;

Tester::it('Duplicate command', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar/test foo/bar/foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` duplicate to `foo/bar/foo`.');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test'), getCommandTemplate('foo')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Duplicate component', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar foo/foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` duplicate to `foo/foo`.');
  $tester->assertDirExist('foo/foo/files');
  $tester->assertFileExist('foo/foo/Containerfile');
  $tester->assertFileContent('foo/foo/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Duplicate domain', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `foo` duplicate to `bar`.');
  $tester->assertDirExist('bar/bar/files');
  $tester->assertFileExist('bar/bar/Containerfile');
  $tester->assertFileContent('bar/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Duplicate unknown command', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar/test foo/bar/foo');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` does not exist.');
});

Tester::it('Duplicate unknown command bis', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar/test foo/bar/foo');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` does not exist.');
});

Tester::it('Duplicate unknown component', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar foo/foo');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` does not exist.');
});

Tester::it('Duplicate unknown domain', function (ITest $tester) {
  $cr = $tester->run('duplicate foo bar');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `foo` does not exist.');
});

Tester::it('Duplicate component with files', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  file_put_contents('foo/bar/files/foo', 'bar');
  $cr = $tester->run('duplicate foo/bar foo/foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` duplicate to `foo/foo`.');
  $tester->assertDirExist('foo/foo/files');
  $tester->assertFileExist('foo/foo/Containerfile');
  $tester->assertFileExist('foo/foo/files/foo');
  $tester->assertFileContent('foo/foo/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});
