<?php

use Tester\ITest;
use Tester\Tester;

Tester::it('Enable command', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('enable foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been enabled.');
  $testTemplate = getCommandTemplate('test');
  $testTemplate['test']['main']['enabled'] = true;
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Disable command', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test')->getReturn(), 0);
  $tester->assertEqualStrict($tester->run('enable foo/bar/test')->getReturn(), 0);
  $cr = $tester->run('disable foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been disabled.');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Enable all commands', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test foo/bar/foo foo/bar/bar bar/foo/test bar/foo/foo bar/foo/bar')->getReturn(), 0);
  $cr = $tester->run('enable all');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `bar/foo/test` has been enabled.' . PHP_EOL .
    'Command `bar/foo/foo` has been enabled.' . PHP_EOL .
    'Command `bar/foo/bar` has been enabled.' . PHP_EOL .
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/foo` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.');
  $testTemplate = array_merge(getCommandTemplate('test'), getCommandTemplate('foo'), getCommandTemplate('bar'));
  $testTemplate['test']['main']['enabled'] = true;
  $testTemplate['foo']['main']['enabled'] = true;
  $testTemplate['bar']['main']['enabled'] = true;
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  $tester->assertFileContent('bar/foo/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Enable multiple commands', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test foo/bar/foo foo/bar/bar bar/foo/test bar/foo/foo bar/foo/bar')->getReturn(), 0);
  $cr = $tester->run('enable foo/bar/test foo/bar/bar bar/foo/foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.' . PHP_EOL .
    'Command `bar/foo/foo` has been enabled.');

  $testTemplate = array_merge(getCommandTemplate('test'), getCommandTemplate('foo'), getCommandTemplate('bar'));
  $testTemplate['test']['main']['enabled'] = true;
  $testTemplate['bar']['main']['enabled'] = true;
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

  $testTemplate = array_merge(getCommandTemplate('test'), getCommandTemplate('foo'), getCommandTemplate('bar'));
  $testTemplate['foo']['main']['enabled'] = true;
  $tester->assertFileContent('bar/foo/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Enable component', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test foo/bar/foo foo/bar/bar')->getReturn(), 0);
  $cr = $tester->run('enable foo/bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/foo` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.');
  $testTemplate = array_merge(getCommandTemplate('test'), getCommandTemplate('foo'), getCommandTemplate('bar'));
  $testTemplate['test']['main']['enabled'] = true;
  $testTemplate['foo']['main']['enabled'] = true;
  $testTemplate['bar']['main']['enabled'] = true;
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Enable domain', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar/test foo/bar/foo foo/bar/bar foo/foo/test foo/foo/foo foo/foo/bar')->getReturn(), 0);
  $cr = $tester->run('enable foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/foo` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.' . PHP_EOL .
    'Command `foo/foo/test` has been enabled.' . PHP_EOL .
    'Command `foo/foo/foo` has been enabled.' . PHP_EOL .
    'Command `foo/foo/bar` has been enabled.');
  $testTemplate = array_merge(getCommandTemplate('test'), getCommandTemplate('foo'), getCommandTemplate('bar'));
  $testTemplate['test']['main']['enabled'] = true;
  $testTemplate['foo']['main']['enabled'] = true;
  $testTemplate['bar']['main']['enabled'] = true;
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  $tester->assertFileContent('foo/foo/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), $testTemplate),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Enable non existent all', function (ITest $tester): void {
  $cr = $tester->run('enable all');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '');

  $tester->assertEqualStrict($tester->run('create foo')->getReturn(), 0);
  $cr = $tester->run('enable all');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '');

  $tester->assertEqualStrict($tester->run('create foo/bar')->getReturn(), 0);
  $cr = $tester->run('enable all');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '');
});

Tester::it('Enable non existent domain', function (ITest $tester): void {
  $cr = $tester->run('enable foo');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `foo` does not exist.');
});

Tester::it('Enable non existent component', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo')->getReturn(), 0);
  $cr = $tester->run('enable foo/bar');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` does not exist.');
});

Tester::it('Enable non existent command', function (ITest $tester): void {
  $tester->assertEqualStrict($tester->run('create foo/bar')->getReturn(), 0);
  $cr = $tester->run('enable foo/bar/test');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` does not exist.');
});