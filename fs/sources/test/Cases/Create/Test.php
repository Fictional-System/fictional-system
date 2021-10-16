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
  $cr = $tester->run('create foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `foo` has been created.');
  $tester->assertDirExist('foo');
});

Tester::it('Create Component', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(
      getCommandTemplate('default'),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create Command', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create full', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create multiple commands', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/foo foo/bar/bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/foo` has been created.' . PHP_EOL . 'Command `foo/bar/bar` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('foo'), getCommandTemplate('bar')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create commands full', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/test bar/foo/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.' . PHP_EOL . 'Command `bar/foo/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertDirExist('bar/foo/files');
  $tester->assertFileExist('bar/foo/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  $tester->assertFileContent('bar/foo/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create Domain Already Exist', function (ITest $tester): void {
  $cr = $tester->run('create foo');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `foo` has been created.');
  $tester->assertDirExist('foo');

  $cr = $tester->run('create foo');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), '`/app/foo` already exist.');
});

Tester::it('Create Component Already Exist', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `foo/bar` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(
      getCommandTemplate('default'),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

  $cr = $tester->run('create foo/bar');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), '`/app/foo/bar` already exist.');
});

Tester::it('Create Command Already Exist', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(getCommandTemplate('default'), getCommandTemplate('test')),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

  $cr = $tester->run('create foo/bar/test');
  $tester->assertEqualStrict($cr->getReturn(), 1);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` already exist.');
});
