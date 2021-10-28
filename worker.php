<?php

use bvdputte\kirbyAutopublish\Autopublish;

// Bootstrap Kirby (from with the plugin's folder)
$projectRoot = dirname(__DIR__) . '/../../';
require $projectRoot . 'kirby/bootstrap.php';

// Instantiate Kirby
$kirby = new Kirby([
    'options' => [
        'debug' => true,
    ],
    'roots' => [
        'kirby' => $projectRoot . 'kirby',
		// 'content' => $projectRoot. 'content',
        // 'site'    => $projectRoot. 'site',
    ],
]);

// Work the queues
Autopublish::publish();
Autopublish::unpublish();
exit();
