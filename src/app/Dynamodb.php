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
    public function getUsers($dayOver)
    {
        // Build condition get list user send tutorial
        $conditions = '';

        try {
            $params = [
                'TableName' => \Constant::TABLE_USER,
                'ProjectionExpression' => 'username, email, created_at',
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
        $eav[':status'] = ['N' => (string)0];
        $eav[':time'] = ['N' => (string)strtotime('midnight')];
        try {
            $params = [
                'TableName' => \Constant::TABLE_USER,
                'ProjectionExpression' => 'email, username, created_at',
                'FilterExpression' => 'created_at <= :time and status = :status',
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
                $eav = [':status' => ['N' => (string)1]];

                $params = [
                    'TableName' => \Constant::TABLE_USER,
                    'Key' => $key,
                    'UpdateExpression' => "set sent_magazine = :status",
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
    public function createCsv($data, $title = [], $glue = "\t")
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
     * @return bool
     */
    public function updateStatusUser($users)
    {
        try {
            foreach ($users as $user) {
                $item = DynamoMapper::unmarshalItem($user);
                $key = ['username' => ['S' => $item['username']]];
                $eav = [':status' => ['N' => (string)1]];

                $params = [
                    'TableName' => \Constant::TABLE_USER,
                    'Key' => $key,
                    'UpdateExpression' => "set status = :status",
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
}
