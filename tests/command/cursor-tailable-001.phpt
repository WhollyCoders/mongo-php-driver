--TEST--
MongoDB\Driver\Command tailable cursor iteration with maxAwaitTimeMS option
--SKIPIF--
<?php require __DIR__ . "/../utils/basic-skipif.inc"; ?>
<?php NEEDS('REPLICASET'); CLEANUP(REPLICASET); ?>
--FILE--
<?php
require_once __DIR__ . "/../utils/basic.inc";
$manager = new MongoDB\Driver\Manager(REPLICASET);

$manager->executeCommand(DATABASE_NAME, new MongoDB\Driver\Command([
    'create' => COLLECTION_NAME,
    'capped' => true,
    'size' => 1048576,
]));

$bulkWrite = new MongoDB\Driver\BulkWrite;
$bulkWrite->insert(['_id' => 1]);
$manager->executeBulkWrite(NS, $bulkWrite);

$pipeline = [
    [ '$changeStream' => [ 'fullDocument' => 'updateLookup' ] ]
];

$command = new MongoDB\Driver\Command([
    'aggregate' => COLLECTION_NAME,
    'pipeline' => $pipeline,
    'cursor' => ['batchSize' => 0],
], [
    'maxAwaitTimeMS' => 100,
]);

$cursor = $manager->executeCommand(DATABASE_NAME, $command);
$it = new IteratorIterator($cursor);

$it->rewind();
$it->next();

$bulkWrite = new MongoDB\Driver\BulkWrite;
$bulkWrite->insert(['_id' => "new-document"]);
$manager->executeBulkWrite(NS, $bulkWrite);

$startTime = microtime(true);
echo "Awaiting results...\n";
$it->next();
var_dump($it->current()->operationType, $it->current()->documentKey);
printf("Waited for %.6f seconds\n", microtime(true) - $startTime);

$startTime = microtime(true);
echo "Awaiting results...\n";
$it->next();
var_dump($it->current()); /* Should be NULL */
printf("Waited for %.6f seconds\n", microtime(true) - $startTime);

?>
===DONE===
<?php exit(0); ?>
--EXPECTF--
Awaiting results...
string(6) "insert"
object(stdClass)#%d (%d) {
  ["_id"]=>
  string(12) "new-document"
}
Waited for 0.%d seconds
Awaiting results...
NULL
Waited for 0.1%d seconds
===DONE===
