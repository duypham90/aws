<?php

namespace App;

use Aws\S3\S3Client;
use Aws\Sdk;
use SendGrid;

class Container
{
    /**
     * @var Dynamodb
     */
    private $dynamodb;

    /**
     * @var S3Client
     */
    private $s3;

    /**
     * @var SendGridMailClient
     */
    private $sendGrid;

    /**
     * @return Dynamodb
     */
    public function getDynamodb()
    {
        if ($this->dynamodb === null) {
            $this->dynamodb = new Dynamodb((new Sdk([
                'region' => 'us-east-1',
                'version' => '2012-08-10'
            ]))->createDynamoDb());
        }

        return $this->dynamodb;
    }


    /**
     * @return AwsS3Client
     */
    public function getS3()
    {
        if ($this->s3 === null) {
            $this->s3 = new AwsS3Client(new S3Client([
                'region' => 'us-east-1',
                'version' => '2006-03-01'
            ]));
        }

        return $this->s3;
    }

    /**
     * @return SendGridMailClient
     */
    public function getSendGrid()
    {
        if ($this->sendGrid === null) {
            $this->sendGrid = new SendGridMailClient(new SendGrid('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'));
        }

        return $this->sendGrid;
    }
}
