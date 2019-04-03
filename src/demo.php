<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Container;

function handler($event)
{
    $event = validateEvent($event);
    $dayOver = $event['dayOver'];

    $container = new Container();

    $dynamodb = $container->getDynamodb();
    $sendGrid = $container->getSendGrid();

    try {
        // Get list user to send mail
        $data = $dynamodb->getListUserSendMail($dayOver);

        if (!$data) {
            echo 'There are no account to send email';
            return;
        }

        // Send mail Tutorial
        $sendMail = $sendGrid->sendGridMailTutorial($data);

        if (!$sendMail) {
            echo 'Send mail tutorial error';
            return;
        }

        // Update status user has sent email
        if (!$dynamodb->updateStatusSendGridTutorial($data, $dayOver)) {
            echo 'Update status user has sent mail error';
            return;
        }

        echo 'Send mail tutorial success';
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

    if (!in_array($params['dayOver'] ?? null, [
        Constant::UDER_REGISTERED_OVER_5_DAY,
        Constant::UDER_REGISTERED_OVER_7_DAY,
        Constant::UDER_REGISTERED_OVER_20_DAY
    ])) {
        throw new Exception('Input parameter is not correct');
    }

    return $params;
}
