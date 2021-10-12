<?php

use Tester\ITest;
use Tester\Tester;

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
      [
        'default' => [
          'main' => [
            'command' => 'default',
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
      ],
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create Command', function (ITest $tester): void {
  $cr = $tester->run('create test/test/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `test/test/test` has been created.');
  $tester->assertDirExist('test/test/files');
  $tester->assertFileContent('test/test/commands.json',
    json_encode([
      'default' => [
        'main' => [
          'command' => 'default',
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
      ],
      'test' => [
        'main' => [
          'command' => 'test',
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
    ],
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});

Tester::it('Create full', function (ITest $tester): void {
  $cr = $tester->run('create foo/bar/test');

  $tester->assertEqualStrict($cr->getReturn(), 0);
  $tester->assertEqualStrict($cr->getOutputString(), 'Command `foo/bar/test` has been created.');
  $tester->assertDirExist('foo/bar/files');
  $tester->assertFileContent('foo/bar/commands.json',
    json_encode([
      'default' => [
        'main' => [
          'command' => 'default',
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
      ],
      'test' => [
        'main' => [
          'command' => 'test',
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
    ],
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
});
