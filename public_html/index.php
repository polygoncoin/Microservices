<?php
require_once __DIR__ . '/../Microservices.php';

$Microservices = new Microservices();
if ($Microservices->init()) {
    $Microservices->process();
}
$Microservices->outputResults();
