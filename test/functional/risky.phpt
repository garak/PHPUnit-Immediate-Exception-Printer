--TEST--
A test is marked "risky".

--ARGS--
-c test --colors=always test/CapabilitiesTest --filter ::testRisky$

--FILE_EXTERNAL--
PHPUnit runner.php

--EXPECTF--
PHPUnit %s

100% [33;1mR[0m [33;1mScriptFUSIONTest\PHPUnitImmediateExceptionPrinter\CapabilitiesTest::testRisky[0m[32m (%i ms)[0m


Time: %s

[30;43mOK, but incomplete, skipped, or risky tests![0m
[30;43mTests: 1[0m[30;43m, Assertions: 0[0m[30;43m, Risky: 1[0m[30;43m.[0m
