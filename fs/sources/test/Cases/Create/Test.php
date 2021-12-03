<?php

use Samples\Template;
use Tester\ITest;
use Tester\Tester;

Tester::it('Create Domain', function (ITest $tester): void {
  $tester->assertRun('create foo', 0, 'Domain `foo` has been created.');
  $tester->assertDirExist('foo');
});

Tester::it('Create Component', function (ITest $tester): void {
  $tester->assertRun('create foo/bar', 0, 'Component `foo/bar` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate());
});

Tester::it('Create Command', function (ITest $tester): void {
  $tester->assertRun('create foo/bar/test', 0, 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Create full', function (ITest $tester): void {
  $tester->assertRun('create foo/bar/test', 0, 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Create multiple commands', function (ITest $tester): void {
  $tester->assertRun('create foo/bar/foo foo/bar/bar', 0, 'Command `foo/bar/foo` has been created.' . PHP_EOL . 'Command `foo/bar/bar` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate(['foo', 'bar']));
});

Tester::it('Create commands full', function (ITest $tester): void {
  $tester->assertRun('create foo/bar/test bar/foo/test', 0, 'Command `foo/bar/test` has been created.' . PHP_EOL . 'Command `bar/foo/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertDirExist('bar/foo/files');
  $tester->assertFileExist('bar/foo/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate(['test']));
  $tester->assertFileContent('bar/foo/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Create Domain Already Exist', function (ITest $tester): void {
  $tester->assertRun('create foo', 0, 'Domain `foo` has been created.');
  $tester->assertDirExist('foo');
  $tester->assertRun('create foo', 1, '`/app/foo` already exist.');
});

Tester::it('Create Component Already Exist', function (ITest $tester): void {
  $tester->assertRun('create foo/bar', 0, 'Component `foo/bar` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate([]));
  $tester->assertRun('create foo/bar', 1, '`/app/foo/bar` already exist.');
});

Tester::it('Create Command Already Exist', function (ITest $tester): void {
  $tester->assertRun('create foo/bar/test', 0, 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileExist('foo/bar/Containerfile');
  $tester->assertFileContent('foo/bar/commands.json', Template::getJsonTemplate(['test']));
  $tester->assertRun('create foo/bar/test', 1, 'Command `foo/bar/test` already exist.');
});
