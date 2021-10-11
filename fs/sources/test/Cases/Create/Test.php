<?php

use Tester\ITester;
use Tester\Tester;

/**
 * TODO: Check file and directory content and cascade creation
 */

Tester::it('Create Domain', function (ITester $tester): bool {
  $cr = $tester->call('create test');
  if (($cr->getReturn() !== 0) ||
    ($cr->getOutputString() !== 'Domain `test` has been created.'))
  {
    return false;
  }
  return true;
});

Tester::it('Create Component', function (ITester $tester): bool {
  $cr = $tester->call('create test/test');
  if (($cr->getReturn() !== 0) ||
    ($cr->getOutputString() !== 'Component `test/test` has been created.'))
  {
    return false;
  }
  return true;
});

Tester::it('Create Command', function (ITester $tester): bool {
  $cr = $tester->call('create test/test/test');
  if (($cr->getReturn() !== 0) ||
    ($cr->getOutputString() !== 'Command `test/test/test` has been created.'))
  {
    return false;
  }
  return true;
});

Tester::it('Create full', function (ITester $tester): bool {
  $cr = $tester->call('create foo/bar/test');
  if (($cr->getReturn() !== 0) ||
    ($cr->getOutputString() !== 'Command `foo/bar/test` has been created.'))
  {
    return false;
  }
  return true;
});
