<?php

use Samples\Template;
use Tester\ITest;
use Tester\Tester;

Tester::it('Duplicate command', function (ITest $tester) {
  $tester->shadowRun('create foo/bar/test');
  $tester->assertRun('duplicate foo/bar/test foo/bar/foo', 0, 'Command `foo/bar/test` duplicate to `foo/bar/foo`.');
  $tester->assertFileContent('foo/bar/commands.json', Template::getTemplate(['test'])->addCommand('foo', 'test')->toJson());
});

Tester::it('Duplicate command bis', function (ITest $tester) {
  $tester->shadowRun('create foo/bar/test');
  $tester->assertRun('duplicate foo/bar/test bar/foo/test', 0, 'Command `foo/bar/test` duplicate to `bar/foo/test`.');
  $tester->assertDirExist('bar/foo/files');
  $tester->assertFileExist('bar/foo/Containerfile');
  $tester->assertFileContent('bar/foo/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Duplicate component', function (ITest $tester) {
  $tester->shadowRun('create foo/bar/test');
  $tester->assertRun('duplicate foo/bar foo/foo', 0, 'Component `foo/bar` duplicate to `foo/foo`.');
  $tester->assertDirExist('foo/foo/files');
  $tester->assertFileExist('foo/foo/Containerfile');
  $tester->assertFileContent('foo/foo/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Duplicate domain', function (ITest $tester) {
  $tester->shadowRun('create foo/bar/test');
  $tester->assertRun('duplicate foo bar', 0, 'Domain `foo` duplicate to `bar`.');
  $tester->assertDirExist('bar/bar/files');
  $tester->assertFileExist('bar/bar/Containerfile');
  $tester->assertFileContent('bar/bar/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Duplicate unknown command', function (ITest $tester) {
  $tester->shadowRun('create foo/bar');
  $tester->assertRun('duplicate foo/bar/test foo/bar/foo', 1, 'Command `foo/bar/test` does not exist.');
});

Tester::it('Duplicate unknown command bis', function (ITest $tester) {
  $tester->shadowRun('create foo');
  $tester->assertRun('duplicate foo/bar/test foo/bar/foo', 1, 'Component `foo/bar` does not exist.');
});

Tester::it('Duplicate unknown component', function (ITest $tester) {
  $tester->shadowRun('create foo');
  $tester->assertRun('duplicate foo/bar foo/foo', 1, 'Component `foo/bar` does not exist.');
});

Tester::it('Duplicate unknown domain', function (ITest $tester) {
  $tester->assertRun('duplicate foo bar', 1, 'Domain `foo` does not exist.');
});

Tester::it('Duplicate component with files', function (ITest $tester) {
  $tester->shadowRun('create foo/bar/test');
  file_put_contents('foo/bar/files/foo', 'bar');
  $tester->assertRun('duplicate foo/bar foo/foo', 0, 'Component `foo/bar` duplicate to `foo/foo`.');
  $tester->assertDirExist('foo/foo/files');
  $tester->assertFileExist('foo/foo/Containerfile');
  $tester->assertFileExist('foo/foo/files/foo');
  $tester->assertFileContent('foo/foo/files/foo', 'bar');
  $tester->assertFileContent('foo/foo/commands.json', Template::getJsonTemplate(['test']));
});

Tester::it('Duplicate already exist command', function (ITest $tester) {
  $tester->shadowRun('create foo/bar/test foo/bar/foo');
  $tester->assertRun('duplicate foo/bar/test foo/bar/foo', 1, 'Command `foo/bar/foo` already exist.');
});

Tester::it('Duplicate already exist command bis', function (ITest $tester) {
  $tester->shadowRun('create foo/bar/test foo/foo/test');
  $tester->assertRun('duplicate foo/bar/test foo/foo/test', 1, 'Command `foo/foo/test` already exist.');
});

Tester::it('Duplicate already exist component', function (ITest $tester) {
  $tester->shadowRun('create foo/bar foo/foo');
  $tester->assertRun('duplicate foo/bar foo/foo', 1, 'Component `foo/foo` already exist.');
});

Tester::it('Duplicate already exist domain', function (ITest $tester) {
  $tester->shadowRun('create foo bar');
  $tester->assertRun('duplicate foo bar', 1, 'Domain `bar` already exist.');
});

Tester::it('Duplicate members level error', function (ITest $tester) {
  $tester->assertRun('duplicate foo/bar bar', 1, 'Each member must have the same level.');
});
Tester::it('Duplicate unknown level', function (ITest $tester) {
  $tester->assertRun('duplicate foo/bar/foo/bar foo/bar/foo/bar', 1, 'Unknown level.');
});
