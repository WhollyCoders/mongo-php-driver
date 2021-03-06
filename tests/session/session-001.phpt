--TEST--
MongoDB\Driver\Session spec test: Pool is LIFO
--SKIPIF--
<?php require __DIR__ . "/../utils/basic-skipif.inc"; ?>
<?php NEEDS_CRYPTO(); ?>
<?php NEEDS('STANDALONE'); NEEDS_ATLEAST_MONGODB_VERSION(STANDALONE, "3.6"); ?>
--FILE--
<?php
require_once __DIR__ . "/../utils/basic.inc";

$manager = new MongoDB\Driver\Manager(STANDALONE);

$firstSession = $manager->startSession();
$firstSessionId = $firstSession->getLogicalSessionId();

unset($firstSession);

$secondSession = $manager->startSession();
$secondSessionId = $secondSession->getLogicalSessionId();

var_dump($firstSessionId == $secondSessionId);

?>
===DONE===
<?php exit(0); ?>
--EXPECT--
bool(true)
===DONE===
