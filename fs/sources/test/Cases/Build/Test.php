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

  $tester->assertRun('build', 0, '1 commands to build.');
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

  $tester->assertRun('build', 0, '3 commands to build.');
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

Tester::it('Multiple build override parameters', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/bar/foo foo/bar/bar');
  $tester->shadowRun('enable foo/bar/test foo/bar/foo foo/bar/bar');
  $config = new Config('foo/bar/commands.json');
  $config['default']['arguments']['argument'] = 'default-version';
  $config['default']['tags']['latest']['arguments']['argument'] = 'default';
  $config['default']['tags']['before'] = [
    'arguments' => [
      'argument' => 'before'
    ],
    'volumes' => ['test:test'],
  ];
  $config['commands']['foo']['volumes'] = [
    'foo:/app',
  ];
  $config['commands']['foo']['tags'] = [
    'before' => [
      'volumes' => [
        'old:old',
      ]
    ]
  ];
  $config['commands']['bar']['interactive'] = true;
  $config['commands']['bar']['tags'] = [
    'before' => [
      'interactive' => false,
    ]
  ];
  $config->save();

  $tester->assertRun('build', 0, '6 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'argument=argument default' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar' . PHP_EOL .
    'tag=before' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'argument=argument before' . PHP_EOL .
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
          '$PWD:/app:z',
          'foo:/app',
        ],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'foo',
      ],
      'bar' => [
        'volumes' => [
          '$PWD:/app:z'
        ],
        'ports' => [],
        'interactive' => true,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'bar',
      ]
    ],
    'foo/bar:before' => [
      'test' => [
        'volumes' => [
          '$PWD:/app:z',
          'test:test',
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
          '$PWD:/app:z',
          'foo:/app',
          'test:test',
          'old:old'
        ],
        'ports' => [],
        'interactive' => false,
        'detached' => false,
        'match_ids' => false,
        'workdir' => '/app',
        'command' => 'foo',
      ],
      'bar' => [
        'volumes' => [
          '$PWD:/app:z',
          'test:test',
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

Tester::it('Simple dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/foo/foo foo/bar/bar');
  $tester->shadowRun('enable foo/foo/foo foo/bar/bar');

  $config = new Config('foo/foo/commands.json');
  $config['default']['from'] = ['foo/bar'];
  $config->save();

  $tester->assertRun('build', 0, '2 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/bar' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/foo' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'build' . PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar:latest' => [
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
    'foo/foo:latest' => [
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
    ]
  ]));
});

Tester::it('Multi Level dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/foo/foo foo/bar/bar foo/test/test');
  $tester->shadowRun('enable foo/foo/foo foo/bar/bar foo/test/test');

  $config = new Config('foo/foo/commands.json');
  $config['default']['from'] = ['foo/bar'];
  $config->save();
  $config = new Config('foo/bar/commands.json');
  $config['default']['from'] = ['foo/test'];
  $config->save();

  $tester->assertRun('build', 0, '3 commands to build.');
  $tester->assertFileExist('build.cache');
  $tester->assertFileContent('build.cache',
    'name=foo/test' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/bar' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'build' . PHP_EOL .
    'name=foo/foo' . PHP_EOL .
    'tag=latest' . PHP_EOL .
    'argument=FROM_TAG latest' . PHP_EOL .
    'build' . PHP_EOL);
  $tester->assertFileExist('commands.cache');
  $tester->assertFileContent('commands.cache', Template::arrayToJson([
    'foo/bar:latest' => [
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
    'foo/foo:latest' => [
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
    'foo/test:latest' => [
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
    ],
  ]));
});

Tester::it('Non existent dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo');
  $tester->shadowRun('enable foo/bar/foo');

  $config = new Config('foo/bar/commands.json');
  $config['default']['from'] = ['foo/foo'];
  $config->save();

  $tester->assertRun('build', 1, 'Component `foo/foo:latest` not found for `foo/bar:latest`.');
});

Tester::it('Circular dependency', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo foo/foo/foo');
  $tester->shadowRun('enable foo/bar/foo foo/foo/foo');

  $config = new Config('foo/bar/commands.json');
  $config['default']['from'] = ['foo/foo'];
  $config->save();
  $config = new Config('foo/foo/commands.json');
  $config['default']['from'] = ['foo/bar'];
  $config->save();

  $tester->assertRun('build', 1, 'Circular dependency detected in `foo/bar:latest`.');
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

Tester::it('Ignore hidden folder', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('enable foo/bar/test');
  $tester->mkdir('foo/.test');

  $tester->shadowRun('build');
});
