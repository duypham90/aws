<?php

require '/opt/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Container;

$container = new Container();

$dynamodb = $container->getDynamodb();
$s3 = $container->getS3();

try {
    // Get list user to send mail magazine
    $data = $dynamodb->getListMailManazine();

    if (!$data) {
        echo 'There are no accounts to send magazine';
        exit;
    }

    // Create csv content
    $csv = $dynamodb->createCsvMagazine($data, ['email', 'username']);

    // Upload csv to AwsS3
    if (!$s3->uploadToS3($csv)) {
        echo 'Upload csv to S3 error';
        exit;
    }

    // Update status user has sent email magazine
    if (!$dynamodb->updateStatusSendMailMagazine($data)) {
        echo 'Update status user has sent mail error';
        exit;
    }

    echo 'Send mail magazine success';
} catch (Exception $e) {
    echo 'Caught exception: ' . $e->getMessage() . "\n";
}


