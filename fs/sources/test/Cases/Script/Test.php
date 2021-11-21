<?php

use Samples\Config;
use Samples\Script;
use Tester\ITest;
use Tester\Tester;

Tester::it('No cache', function (ITest $tester): void {
  $tester->assertRun('script all', 1, 'Command cache not found. Run `fs build` before.');
});

Tester::it('Bad volume format', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo');
  $tester->shadowRun('enable foo');

  $config = new Config('foo/bar/commands.json');
  $config['default']['volumes'][] = 'foo:bar:foo:bar';
  $config->save();
  $tester->shadowRun('build');

  $tester->assertRun('script all', 1, 'Bad format in volumes definition for `foo/bar/foo:latest`.');
});

Tester::it('No script to generate', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test');
  $tester->shadowRun('build');

  $tester->assertRun('script all', 0, '0 scripts generated.');
});

Tester::it('Generate all scripts', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo foo/bar/bar');
  $tester->shadowRun('enable foo');
  $tester->shadowRun('build');

  mkdir('bin', 0700);
  $tester->assertRun('script all', 0, '2 scripts generated.');
  $tester->assertFileExist('bin/foo');
  $tester->assertFileContent('bin/foo',
    Script::get('foo/bar/foo', 'latest', 'foo')
      ->addVolume('$PWD:/app')
      ->getScript()
  );
  $tester->assertFileExist('bin/bar');
  $tester->assertFileContent('bin/bar',
    Script::get('foo/bar/bar', 'latest', 'bar')
      ->addVolume('$PWD:/app')
      ->getScript()
  );
});
