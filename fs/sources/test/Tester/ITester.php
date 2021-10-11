<?php

namespace Tester;

interface ITester
{
  public function call(...$args): TestReturn;
}
