<?php

namespace App;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class AwsS3Client
{
    /**
     * @var $s3Client S3Client
     */
    private $s3Client;

    /**
     * S3Client constructor.
     * @param $s3
     */
    public function __construct(S3Client $s3)
    {
        $this->s3Client = $s3;
    }

    /**
     * @param $data
     * @return bool
     */
    public function uploadToS3($data)
    {
        $filePath = time() . '.csv';

        try {
            $result = $this->s3Client->putObject(array(
                'Bucket' => 'duy-test-upload-s3',
                'Key' => $filePath,
                'Body' => $data
            ));

            if (!$result["@metadata"]["statusCode"] == '200') {
                return false;
            }

            return true;
        } catch (S3Exception $exception) {
            echo $exception->getMessage();
            return false;
        }
    }
}
