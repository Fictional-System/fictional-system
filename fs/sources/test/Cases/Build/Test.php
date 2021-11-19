<?php

use Samples\Config;
use Tester\ITest;
use Tester\Tester;

Tester::it('Nothing to build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');

  $tester->assertRun('build', 0, '0 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache', '');
});

Tester::it('Simple build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL);
});

Tester::it('Complete build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $config = new Config('foo/bar/commands.json');
  $config['default']['arguments']['argument'] = 'value';
  $config->save();

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'argument=argument:value' . PHP_EOL .
    'build' . PHP_EOL);
});

Tester::it('Multiple build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar test/bar/bar');
  $tester->shadowRun('enable foo/bar/test foo/bar/foo test/bar/bar');

  $config = new Config('test/bar/commands.json');
  $config['default']['arguments']['argument'] = 'value';
  $config->save();

  $tester->assertRun('build', 0, '3 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/foo' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL .
    'name=test/bar/bar' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=test/bar' . PHP_EOL .
    'argument=argument:value' . PHP_EOL .
    'build' . PHP_EOL);
});

Tester::it('Multiple build override arguments', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar');
  $tester->shadowRun('enable foo/bar/test foo/bar/foo foo/bar/bar');

  $config = new Config('foo/bar/commands.json');
  $config['default']['arguments']['argument'] = 'default';
  $config['default']['versions']['before'] = [
    'arguments' => [
      'argument' => 'default-version'
    ]
  ];
  $config['commands']['foo']['arguments'] = [
    'argument' => 'foo'
  ];
  $config['commands']['foo']['versions'] = [
    'before' => [
      'arguments' => [
        'argument' => 'foo-version'
      ]
    ]
  ];
  $config['commands']['bar']['arguments'] = [];
  $config['commands']['bar']['versions'] = [
    'before' => [
      'arguments' => []
    ]
  ];
  $config->save();

  $tester->assertRun('build', 0, '6 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'argument=argument:default' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/test' . PHP_EOL .
    'version=before' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'argument=argument:default-version' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/foo' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'argument=argument:foo' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/foo' . PHP_EOL .
    'version=before' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'argument=argument:foo-version' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/bar' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/bar' . PHP_EOL .
    'version=before' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL);
});

Tester::it('Simple dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo foo/bar/bar');
  $tester->shadowRun('enable foo/bar/foo foo/bar/bar');

  $config = new Config('foo/bar/commands.json');
  $config['commands']['foo']['versions']['latest']['from']= ['foo/bar/bar'];
  $config->save();

  $tester->assertRun('build', 0, '2 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/bar' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/foo' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL);
});
