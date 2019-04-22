<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Container;

function handler($event)
{
    $event = validateEvent($event);
    $dayOver = $event['user'];

    $container = new Container();

    $dynamodb = $container->getDynamodb();

    try {
        // Get list user to send mail
        $data = $dynamodb->getUsers($dayOver);

        if (!$data) {
            echo 'No data';
            return;
        }

        echo 'Test ok';
    } catch (Exception $e) {
        echo 'Caught exception: ' . $e->getMessage() . "\n";
    }
}

/**
 * @param $event
 * @return mixed
 * @throws Exception
 */
function validateEvent($event)
{
    // Get param from callback event
    $params = json_decode($event, true);

    if (!in_array($params['user'] ?? null, [1, 2, 3])) {
        throw new Exception('Input parameter is not correct');
    }

    return $params;
}
