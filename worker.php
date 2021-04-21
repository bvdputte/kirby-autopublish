<?php

use bvdputte\kirbyAutopublish\Autopublish;

// Bootstrap Kirby (from with the plugin's folder)
require '../../../kirby/bootstrap.php';
$kirbyPath = dirname(__FILE__) . "/../../../../";

// Instantiate Kirby
$kirby = new Kirby([
    'options' => [
        'debug' => true,
    ],
    'roots' => [
        'kirby' => $kirbyPath
    ],
]);

// Work the queues
Autopublish::publish();
Autopublish::unpublish();
exit();
