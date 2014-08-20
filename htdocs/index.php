<?php

namespace Kagency\CouchdbEndpoint;

require __DIR__ . '/../vendor/autoload.php';

$dic = new Container();
$endpoint = new Endpoint\Symfony(
    $dic->get('Kagency.CouchdbEndpoint.Replicator'),
    "master"
);
$endpoint->run();
