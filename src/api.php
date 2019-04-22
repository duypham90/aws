<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Container;

function handler($event)
{
    $content = json_decode($event, true);

    // Read file response to know which file need to get
    $method = $content['httpMethod'];
    $body = $content['body'];
    
    $event = validateEvent($httpMethod);

    try {
        return [
            'statusCode' => 200,
            'body' => $body
        ];
    } catch (Exception $e) {
        echo 'Caught exception: ' . $e->getMessage() . "\n";
    }
}

