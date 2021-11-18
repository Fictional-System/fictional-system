<?php

use Samples\Template;
use Tester\ITest;
use Tester\Tester;

Tester::it('Enable command', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $cr = $tester->run('enable foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been enabled.');
  $tester->assertFileContent('foo/bar/commands.json', Template::getTemplate(['test'])->enableCommand('test')->toJson());
});

Tester::it('Disable command', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');
  $cr = $tester->run('disable foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been disabled.');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Enable all commands', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar bar/foo/test bar/foo/foo bar/foo/bar');
  $cr = $tester->run('enable all');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `bar/foo/test` has been enabled.' . PHP_EOL .
    'Command `bar/foo/foo` has been enabled.' . PHP_EOL .
    'Command `bar/foo/bar` has been enabled.' . PHP_EOL .
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/foo` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.');
  $testTemplate = Template::getTemplate(['test', 'foo', 'bar'])
    ->enableCommands(['test', 'foo', 'bar'])
    ->toJson();
  $tester->assertFileContent('foo/bar/commands.json', $testTemplate);
  $tester->assertFileContent('bar/foo/commands.json', $testTemplate);
});

Tester::it('Enable multiple commands', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar bar/foo/test bar/foo/foo bar/foo/bar');
  $cr = $tester->run('enable foo/bar/test foo/bar/bar bar/foo/foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.' . PHP_EOL .
    'Command `bar/foo/foo` has been enabled.');

  $tester->assertFileContent('foo/bar/commands.json',
    Template::getTemplate(['test', 'foo', 'bar'])
      ->enableCommands(['test', 'bar'])
      ->toJson());
  $tester->assertFileContent('bar/foo/commands.json',
    Template::getTemplate(['test', 'foo', 'bar'])
      ->enableCommand('foo')
      ->toJson());
});

Tester::it('Enable component', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar');
  $cr = $tester->run('enable foo/bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/foo` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.');
  $tester->assertFileContent('foo/bar/commands.json',
    Template::getTemplate(['test', 'foo', 'bar'])
      ->enableCommands(['test', 'foo', 'bar'])
      ->toJson());
});

Tester::it('Enable domain', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar foo/foo/test foo/foo/foo foo/foo/bar');
  $cr = $tester->run('enable foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(),
    'Command `foo/bar/test` has been enabled.' . PHP_EOL .
    'Command `foo/bar/foo` has been enabled.' . PHP_EOL .
    'Command `foo/bar/bar` has been enabled.' . PHP_EOL .
    'Command `foo/foo/test` has been enabled.' . PHP_EOL .
    'Command `foo/foo/foo` has been enabled.' . PHP_EOL .
    'Command `foo/foo/bar` has been enabled.');
  $testTemplate = Template::getTemplate(['test', 'foo', 'bar'])
    ->enableCommands(['test', 'foo', 'bar'])
    ->toJson();
  $tester->assertFileContent('foo/bar/commands.json', $testTemplate);
  $tester->assertFileContent('foo/foo/commands.json', $testTemplate);
});

Tester::it('Enable non existent all', function (ITest $tester): void {
  $cr = $tester->run('enable all');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '');

  $tester->shadowRun('create foo');
  $cr = $tester->run('enable all');
  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), '');

  $tester->shadowRun('create foo/bar');
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
  $tester->shadowRun('create foo');
  $cr = $tester->run('enable foo/bar');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` does not exist.');
});

Tester::it('Enable non existent command', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar');
  $cr = $tester->run('enable foo/bar/test');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` does not exist.');
});
