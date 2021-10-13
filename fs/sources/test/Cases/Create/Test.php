<?php

use Tester\ITest;
use Tester\Tester;

function getCommandTemplate(string $name)
{
  return json_decode(str_replace('#command#', $name, json_encode([
    '#command#' => [
      'main' => [
        'command' => '#command#',
        'enabled' => false,
        'versions' => ['latest'],
        'from' => [],
      ],
      'options' => [
        'volumes' => ['$PWD:/app'],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match-ids' => false,
        'workdir' => '/app'
      ],
      'arguments' => [],
      'env' => [],
    ]
  ])), true);
}

Tester::it('Create Domain', function (ITest $tester): void {
  $cr = $tester->run('create test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `test` has been created.');
  $tester->assertDirExist('test');
});

Tester::it('Create Component', function (ITest $tester): void {
  $cr = $tester->run('create test/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `test/test` has been created.');
  $tester->assertDirExist('test/test/files');
  $tester->assertFileContent('test/test/commands.json',
    json_encode(
      getCommandTemplate('default'),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create Command', function (ITest $tester): void {
  $cr = $tester->run('create test/test/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `test/test/test` has been created.');
  $tester->assertDirExist('test/test/files');
  $tester->assertFileContent('test/test/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create full', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create multiple commands', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/foo foo/bar/bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/foo` has been created.' . PHP_EOL . 'Command `foo/bar/bar` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('foo'), getCommandTemplate('bar')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create commands full', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/test bar/foo/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.' . PHP_EOL . 'Command `bar/foo/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertDirExist('bar/foo/files');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  $tester->assertFileContent('bar/foo/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});
