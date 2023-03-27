<?php

use Samples\Config;
use Samples\Template;
use Tester\ITest;
use Tester\Tester;

Tester::it('Nothing to build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');

  $tester->shadowRun('build');
  $tester->assertRun('rebuild', 0, '0 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache', PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([]));
});

Tester::it('Simple build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $tester->shadowRun('build');
  $tester->assertRun('rebuild', 0, '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'build' . PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar:latest' => [
      'test' => [
        'volumes' => [
          '$PWD:/app:z'
        ],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'test',
      ],
    ]
  ]));
});

Tester::it('Complete build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $config = new Config('foo/bar/commands.json');
  $config['default']['arguments']['argument'] = 'value';
  $config->save();

  $tester->shadowRun('build');
  $tester->assertRun('rebuild', 0, '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'argument=argument value' . PHP_EOL .
    'build' . PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar:latest' => [
      'test' => [
        'volumes' => [
          '$PWD:/app:z'
        ],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'test',
      ],
    ]
  ]));
});

Tester::it('Multiple build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar test/bar/bar');
  $tester->shadowRun('enable foo/bar/test foo/bar/foo test/bar/bar');

  $config = new Config('test/bar/commands.json');
  $config['default']['arguments']['argument'] = 'value';
  $config->save();

  $tester->shadowRun('build');
  $tester->assertRun('rebuild', 0, '3 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'build' . PHP_EOL .
    'name=test/bar' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'argument=argument value' . PHP_EOL .
    'build' . PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar:latest' => [
      'test' => [
        'volumes' => [
          '$PWD:/app:z'
        ],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'test',
      ],
      'foo' => [
        'volumes' => [
          '$PWD:/app:z'
        ],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'foo',
      ],
    ],
    'test/bar:latest' => [
      'bar' => [
        'volumes' => [
          '$PWD:/app:z'
        ],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'bar',
      ],
    ],
  ]));
});
