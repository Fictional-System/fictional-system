<?php

use Samples\Config;
use Samples\Template;
use Tester\ITest;
use Tester\Tester;

Tester::it('Nothing to build', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');

  $tester->assertRun('build', 0, '0 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache', PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([]));
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
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar/test:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'test',
    ]
  ]));
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
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar/test:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'test',
    ]
  ]));
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
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar/test:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'test',
    ],
    'foo/bar/foo:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'foo',
    ],
    'test/bar/bar:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'bar',
    ]
  ]));
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
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar/test:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'test',
    ],
    'foo/bar/test:before' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'test',
    ],
    'foo/bar/foo:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'foo',
    ],
    'foo/bar/foo:before' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'foo',
    ],
    'foo/bar/bar:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'bar',
    ],
    'foo/bar/bar:before' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'bar',
    ]
  ]));
});

Tester::it('Simple dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo foo/bar/bar');
  $tester->shadowRun('enable foo/bar/foo foo/bar/bar');

  $config = new Config('foo/bar/commands.json');
  $config['commands']['foo']['versions']['latest']['from'] = ['foo/bar/bar'];
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
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar/foo:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'foo',
    ],
    'foo/bar/bar:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'bar',
    ]
  ]));
});

Tester::it('Multi Level dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo foo/bar/bar foo/bar/test');
  $tester->shadowRun('enable foo/bar/foo foo/bar/bar foo/bar/test');

  $config = new Config('foo/bar/commands.json');
  $config['commands']['foo']['versions']['latest']['from'] = ['foo/bar/bar'];
  $config['commands']['bar']['versions']['latest']['from'] = ['foo/bar/test'];
  $config->save();

  $tester->assertRun('build', 0, '3 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar/test' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/bar' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar/foo' . PHP_EOL .
    'version=latest' . PHP_EOL .
    'context=foo/bar' . PHP_EOL .
    'build' . PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar/foo:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'foo',
    ],
    'foo/bar/bar:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'bar',
    ],
    'foo/bar/test:latest' => [
      'env' => [],
      'volumes' => [
        '$PWD:/app'
      ],
      'ports' => [],
      'interactive' => false,
      'detached' => false,
      'match-ids' => false,
      'workdir' => '/app',
      'command' => 'test',
    ]
  ]));
});

Tester::it('Non existent dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo');
  $tester->shadowRun('enable foo/bar/foo');

  $config = new Config('foo/bar/commands.json');
  $config['commands']['foo']['versions']['latest']['from'] = ['foo/bar/bar'];
  $config->save();

  $tester->assertRun('build', 1, 'Command `foo/bar/bar:latest` not found for `foo/bar/foo:latest`.');
});

Tester::it('Circular dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo foo/bar/bar');
  $tester->shadowRun('enable foo/bar/foo foo/bar/bar');

  $config = new Config('foo/bar/commands.json');
  $config['commands']['foo']['versions']['latest']['from'] = ['foo/bar/bar'];
  $config['commands']['bar']['versions']['latest']['from'] = ['foo/bar/foo'];
  $config->save();

  $tester->assertRun('build', 1, 'Circular dependency detected in `foo/bar/foo:latest`.');
});

Tester::it('Simple file', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  file_put_contents('foo/bar/files/foo', 'bar');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'bar');
});

Tester::it('Override file', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  file_put_contents('foo/bar/files/foo', 'bar');
  $tester->mkdir('foo/bar/local');
  file_put_contents('foo/bar/local/foo', 'foo');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'foo');
});

Tester::it('Override multiple file', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  file_put_contents('foo/bar/files/foo', 'bar');
  file_put_contents('foo/bar/files/bar', 'foo');
  file_put_contents('foo/bar/files/test', 'test');
  $tester->mkdir('foo/bar/local');
  file_put_contents('foo/bar/local/foo', 'foo');
  file_put_contents('foo/bar/local/bar', 'bar');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'foo');
  $tester->assertFileExist('foo/bar/cache/bar');
  $tester->assertFileContent('foo/bar/cache/bar', 'bar');
  $tester->assertFileExist('foo/bar/cache/test');
  $tester->assertFileContent('foo/bar/cache/test', 'test');
});

Tester::it('Simple directory', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $tester->mkdir('foo/bar/files/foo');
  file_put_contents('foo/bar/files/foo/bar', 'bar');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('foo/bar/cache/foo/bar');
  $tester->assertFileContent('foo/bar/cache/foo/bar', 'bar');
});

Tester::it('Override directory', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $tester->mkdir('foo/bar/files/foo');
  file_put_contents('foo/bar/files/foo/bar', 'bar');
  $tester->mkdir('foo/bar/local/foo');
  file_put_contents('foo/bar/local/foo/bar', 'foo');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('foo/bar/cache/foo/bar');
  $tester->assertFileContent('foo/bar/cache/foo/bar', 'foo');
});

Tester::it('Multiple files in directory', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  $tester->mkdir('foo/bar/files/foo');
  file_put_contents('foo/bar/files/foo/foo', 'bar');
  file_put_contents('foo/bar/files/foo/bar', 'foo');
  file_put_contents('foo/bar/files/foo/test', 'test');
  $tester->mkdir('foo/bar/local/foo');
  file_put_contents('foo/bar/local/foo/foo', 'foo');
  file_put_contents('foo/bar/local/foo/bar', 'bar');

  $tester->assertRun('build', 0, '1 commands to build.');
  $tester->assertFileExist('foo/bar/cache/foo/foo');
  $tester->assertFileContent('foo/bar/cache/foo/foo', 'foo');
  $tester->assertFileExist('foo/bar/cache/foo/bar');
  $tester->assertFileContent('foo/bar/cache/foo/bar', 'bar');
  $tester->assertFileExist('foo/bar/cache/foo/test');
  $tester->assertFileContent('foo/bar/cache/foo/test', 'test');
});

Tester::it('Generate files', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  file_put_contents('foo/bar/files/foo', 'bar');

  $tester->shadowRun('build');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'bar');

  $tester->mkdir('foo/bar/local');
  file_put_contents('foo/bar/local/foo', 'foo');

  $tester->shadowRun('build');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'foo');

  file_put_contents('foo/bar/local/foo', 'test');

  $tester->shadowRun('build');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'test');
});

Tester::it('Remove files', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');

  file_put_contents('foo/bar/files/foo', 'bar');
  $tester->mkdir('foo/bar/local');
  file_put_contents('foo/bar/local/foo', 'foo');

  $tester->shadowRun('build');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'foo');

  file_put_contents('foo/bar/local/bar', 'bar');
  unlink('foo/bar/local/foo');

  $tester->shadowRun('build');
  $tester->assertFileExist('foo/bar/cache/foo');
  $tester->assertFileContent('foo/bar/cache/foo', 'bar');
  $tester->assertFileExist('foo/bar/cache/bar');
  $tester->assertFileContent('foo/bar/cache/bar', 'bar');

  unlink('foo/bar/files/foo');

  $tester->shadowRun('build');
  $tester->assertFileNotExist('foo/bar/cache/foo');
  $tester->assertFileExist('foo/bar/cache/bar');
  $tester->assertFileContent('foo/bar/cache/bar', 'bar');
});

