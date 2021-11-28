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

  $tester->mkdir('bin');
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

Tester::it('Duplicate command in same domain', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test foo/foo/test');
  $tester->shadowRun('enable foo/bar/test foo/foo/test');
  $tester->shadowRun('build');

  $tester->mkdir('bin');
  $tester->assertRun('script all', 0, '2 scripts generated.');
  $tester->assertFileExist('bin/bar_test');
  $tester->assertFileContent('bin/bar_test',
    Script::get('foo/bar/test', 'latest', 'test')
      ->addVolume('$PWD:/app')
      ->getScript()
  );
  $tester->assertFileExist('bin/foo_test');
  $tester->assertFileContent('bin/foo_test',
    Script::get('foo/foo/test', 'latest', 'test')
      ->addVolume('$PWD:/app')
      ->getScript()
  );
});

Tester::it('Duplicate command in other domain', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/test bar/bar/test');
  $tester->shadowRun('enable foo/bar/test bar/bar/test');
  $tester->shadowRun('build');

  $tester->mkdir('bin');
  $tester->assertRun('script all', 0, '2 scripts generated.');
  $tester->assertFileExist('bin/foo_bar_test');
  $tester->assertFileContent('bin/foo_bar_test',
    Script::get('foo/bar/test', 'latest', 'test')
      ->addVolume('$PWD:/app')
      ->getScript()
  );
  $tester->assertFileExist('bin/bar_bar_test');
  $tester->assertFileContent('bin/bar_bar_test',
    Script::get('bar/bar/test', 'latest', 'test')
      ->addVolume('$PWD:/app')
      ->getScript()
  );
});

Tester::it('Use env file', function (ITest $tester): void {
  $tester->shadowRun('create foo/bar/foo foo/bar/bar');
  $tester->shadowRun('enable foo/bar/foo foo/bar/bar');
  file_put_contents('foo/bar/files/foo.env', 'FOO=bar');
  $tester->shadowRun('build');

  $tester->mkdir('bin');
  $tester->assertRun('script all', 0, '2 scripts generated.');
  $tester->assertFileExist('bin/foo');
  $tester->assertFileContent('bin/foo',
    Script::get('foo/bar/foo', 'latest', 'foo')
      ->addVolume('$PWD:/app')
      ->addEnvFile('/app/foo/bar/cache/foo.env')
      ->getScript()
  );
  $tester->assertFileExist('bin/bar');
  $tester->assertFileContent('bin/bar',
    Script::get('foo/bar/bar', 'latest', 'bar')
      ->addVolume('$PWD:/app')
      ->getScript()
  );
});

Tester::it('Script interactive', function (ITest $tester): void {

});

Tester::it('Script detached', function (ITest $tester): void {

});

Tester::it('Script matchid', function (ITest $tester): void {

});

Tester::it('Script workdir', function (ITest $tester): void {

});
