<?php

$phar = new Phar('/tmp/fs.phar');
$phar->buildFromDirectory(__DIR__ . '/fs');
$phar->setStub($phar->createDefaultStub('run.php'));
$phar->setSignatureAlgorithm(Phar::SHA512);
$phar->stopBuffering();
