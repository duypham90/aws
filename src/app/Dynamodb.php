<?php

namespace App;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class Dynamodb
{
    /**
     * @var $dynamoDb DynamoDbClient
     */
    private $dynamoDb;

    /**
     * Dynamodb constructor.
     * @param $client
     */
    public function __construct($client)
    {
        $this->dynamoDb = $client;
    }

    /**
     * Get list user send mail turorial
     *
     * @param $dayOver
     * @return mixed
     */
    public function getListUserSendMail($dayOver)
    {
        // Build condition get list user send tutorial
        $conditions = $this->buildQueryGetListUserSendMail($dayOver);

        try {
            $params = [
                'TableName' => \Constant::TABLE_USER,
                'ProjectionExpression' => 'username, email, sent_tutorial, created_at',
                'FilterExpression' => $conditions['filter'],
                'ExpressionAttributeValues' => $conditions['eav']
            ];

            $result = $this->dynamoDb->scan($params);

            return $result['Items'];

        } catch (DynamoDbException $e) {
            echo "Unable to create table:\n";
            echo $e->getMessage() . "\n";
        }
    }

    /**
     * @return mixed
     */
    public function getListMailManazine()
    {
        $eav[':sent_magazine'] = ['N' => (string)\Constant::STATUS_SEND_MAIL_MAGAZINE_NOT_YET];
        $eav[':time'] = ['N' => (string)strtotime('midnight')];
        try {
            $params = [
                'TableName' => \Constant::TABLE_USER,
                'ProjectionExpression' => 'email, username, created_at',
                'FilterExpression' => 'created_at <= :time and sent_magazine = :sent_magazine',
                'ExpressionAttributeValues' => $eav
            ];

            $result = $this->dynamoDb->scan($params);

            return $result['Items'];

        } catch (DynamoDbException $e) {
            echo "Unable to create table:\n";
            echo $e->getMessage() . "\n";
        }
    }

    /**
     * @param $users
     * @return bool
     */
    public function updateStatusSendMailMagazine($users)
    {
        try {
            foreach ($users as $user) {
                $item = DynamoMapper::unmarshalItem($user);
                $key = ['username' => ['S' => $item['username']]];
                $eav = [':sent_magazine' => ['N' => (string)\Constant::STATUS_SENT_MAIL_MAGAZINE]];

                $params = [
                    'TableName' => \Constant::TABLE_USER,
                    'Key' => $key,
                    'UpdateExpression' => "set sent_magazine = :sent_magazine",
                    'ExpressionAttributeValues' => $eav,
                    'ReturnValues' => 'UPDATED_NEW'
                ];

                $this->dynamoDb->updateItem($params);
            }

            return true;
        } catch (DynamoDbException $exception) {
            echo $exception->getMessage() . "\n";

            return false;
        }
    }

    /**
     * Create csv data
     * @param array $data
     * @param array $title
     * @param string $glue
     * @return string
     */
    public function createCsvSendGridMail($data, $title = [], $glue = "\t")
    {
        $marshaler = new Marshaler();

        // Create title for csv
        $csv = '';
        if ($title) {
            $csv = implode("\t", $title) . \Constant::CRLF;
        }

        foreach ($data as $item) {
            $csv .= implode($glue, $marshaler->unmarshalItem($item)) . \Constant::CRLF;
        }

        return $csv;
    }

    /**
     * Update status user has sent email
     *
     * @param $users
     * @param $dayOver
     * @return bool
     */
    public function updateStatusSendGridTutorial($users, $dayOver)
    {
        switch ($dayOver) {
            case \Constant::UDER_REGISTERED_OVER_20_DAY:
                $flagSendTutorial = \Constant::SENT_TUTORIAL_MAIL_20_DAY;
                break;
            case \Constant::UDER_REGISTERED_OVER_7_DAY:
                $flagSendTutorial = \Constant::SENT_TUTORIAL_MAIL_7_DAY;
                break;
            default:
                $flagSendTutorial = \Constant::SENT_TUTORIAL_MAIL_5_DAY;
        }

        try {
            foreach ($users as $user) {
                $item = DynamoMapper::unmarshalItem($user);
                $key = ['username' => ['S' => $item['username']]];
                $eav = [':sent_tutorial' => ['N' => (string)$flagSendTutorial]];

                $params = [
                    'TableName' => \Constant::TABLE_USER,
                    'Key' => $key,
                    'UpdateExpression' => "set sent_tutorial = :sent_tutorial",
                    'ExpressionAttributeValues' => $eav,
                    'ReturnValues' => 'UPDATED_NEW'
                ];

                $this->dynamoDb->updateItem($params);
            }

            return true;
        } catch (DynamoDbException $exception) {
            echo $exception->getMessage() . "\n";

            return false;
        }
    }

    /**
     * Create csv data
     * @param array $data
     * @param array $title
     * @param string $glue
     * @return string
     */
    public function createCsvMagazine($data, $title = [], $glue = "\t")
    {
        // Create title for csv
        $csv = '';
        if ($title) {
            $csv = implode("\t", $title) . \Constant::CRLF;
        }

        foreach ($data as $item) {
            $items = DynamoMapper::unmarshalItem($item);
            $csv .= implode($glue, [$items['email'], $items['username']]) . \Constant::CRLF;
        }

        return $csv;
    }

    /**
     * Build query get list user send mail turorial
     *
     * @param $dayOver
     * @return array
     */
    private function buildQueryGetListUserSendMail($dayOver)
    {
        $filter = 'created_at > :start_date and created_at <= :end_date and sent_tutorial = :sent_tutorial';

        $endDate = '';

        switch ($dayOver) {
            case \Constant::UDER_REGISTERED_OVER_20_DAY:
                $startDate = strtotime('midnight -' . \Constant::UDER_REGISTERED_OVER_20_DAY . ' day');
                $filter = 'created_at < :start_date and sent_tutorial = :sent_tutorial';
                $sentTutorial = \Constant::SEND_TUTORIAL_MAIL_20_DAY_NOT_YET;
                break;
            case \Constant::UDER_REGISTERED_OVER_7_DAY:
                $startDate = strtotime('midnight -' . \Constant::UDER_REGISTERED_OVER_20_DAY . ' day');
                $endDate = strtotime('-' . \Constant::UDER_REGISTERED_OVER_7_DAY . ' day');
                $sentTutorial = \Constant::SEND_TUTORIAL_MAIL_7_DAY_NOT_YET;
                break;
            default:
                $startDate = strtotime('midnight -' . \Constant::UDER_REGISTERED_OVER_7_DAY . ' day');
                $endDate = strtotime('-' . \Constant::UDER_REGISTERED_OVER_5_DAY . ' day');
                $sentTutorial = \Constant::SEND_TUTORIAL_MAIL_5_DAY_NOT_YET;
        }

        $eav[':sent_tutorial'] = ['N' => (string)$sentTutorial];
        $eav[':start_date'] = ['N' => (string)$startDate];

        if ($endDate) {
            $eav[':end_date'] = ['N' => (string)$endDate];
        }

        return ['eav' => $eav, 'filter' => $filter];
    }
}
