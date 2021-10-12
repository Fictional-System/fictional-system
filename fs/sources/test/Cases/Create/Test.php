<?php

use Command\Config;
use Tester\ITester;
use Tester\Tester;

Tester::it('Create Domain', function (ITester $tester): void {
  $cr = $tester->call('create test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Domain `test` has been created.');
  $tester->assertDirExist('test');
});

Tester::it('Create Component', function (ITester $tester): void {
  $cr = $tester->call('create test/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Component `test/test` has been created.');
  $tester->assertDirExist('test/test/files');
  $tester->assertFileContent('test/test/commands.json',
    json_encode(Config::getTemplate(['command' => 'default']),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create Command', function (ITester $tester): void {
  $cr = $tester->call('create test/test/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `test/test/test` has been created.');
  $tester->assertDirExist('test/test/files');
  $tester->assertFileContent('test/test/commands.json',
    json_encode(array_merge(
      Config::getTemplate(['command' => 'default']),
      Config::getTemplate(['command' => 'test'])),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create full', function (ITester $tester): void {
  $cr = $tester->call('create foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode(array_merge(
      Config::getTemplate(['command' => 'default']),
      Config::getTemplate(['command' => 'test'])),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});
