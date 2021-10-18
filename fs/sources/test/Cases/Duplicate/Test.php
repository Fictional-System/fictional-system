<?php

use Samples\Template;
use Tester\ITest;
use Tester\Tester;

Tester::it('Duplicate command', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar/test foo/bar/foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` duplicate to `foo/bar/foo`.');
  $tester->assertFileContent('foo/bar/commands.json', Template::getTemplate(['test'])->addCommand('foo', 'test')->toJson());
});

Tester::it('Duplicate command bis', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar/test bar/foo/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` duplicate to `bar/foo/test`.');
  $tester->assertDirExist('bar/foo/files');
  $tester->assertFileExist('bar/foo/Containerfile');
  $tester->assertFileContent('bar/foo/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Duplicate component', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar foo/foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` duplicate to `foo/foo`.');
  $tester->assertDirExist('foo/foo/files');
  $tester->assertFileExist('foo/foo/Containerfile');
  $tester->assertFileContent('foo/foo/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Duplicate domain', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `foo` duplicate to `bar`.');
  $tester->assertDirExist('bar/bar/files');
  $tester->assertFileExist('bar/bar/Containerfile');
  $tester->assertFileContent('bar/bar/commands.json', Template::getJsonTemplate(['test']));
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
  $tester->assertFileContent('foo/foo/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Duplicate already exist command', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test foo/bar/foo')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar/test foo/bar/foo');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/foo` already exist.');
});

Tester::it('Duplicate already exist command bis', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar/test foo/foo/test')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar/test foo/foo/test');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/foo/test` already exist.');
});

Tester::it('Duplicate already exist component', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo/bar foo/foo')->getReturn(), 0);
  $cr = $tester->run('duplicate foo/bar foo/foo');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/foo` already exist.');
});

Tester::it('Duplicate already exist domain', function (ITest $tester) {
  $tester->assertEqualStrict($tester->run('create foo bar')->getReturn(), 0);
  $cr = $tester->run('duplicate foo bar');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `bar` already exist.');
});

Tester::it('Duplicate members level error', function (ITest $tester) {
  $cr = $tester->run('duplicate foo/bar bar');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Each member must have the same level.');
});
Tester::it('Duplicate unknown level', function (ITest $tester) {
  $cr = $tester->run('duplicate foo/bar/foo/bar foo/bar/foo/bar');

  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Unknown level.');
});
