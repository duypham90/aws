<?php
require 'vendor/autoload.php';

use Aws\CloudWatch\CloudWatchClient; 
use Aws\Exception\AwsException;


$client = new Aws\CloudWatch\CloudWatchClient([
    'region' => 'us-east-2',
    'version' => 'latest',
]);

try {
    $result = $client->describeAlarms([
    ]);
    foreach ($result['MetricAlarms'] as $alarm) {
        echo $alarm['AlarmName'] . "\n";
    }
} catch (AwsException $e) {
    // output error message if fails
    error_log($e->getMessage());
}
