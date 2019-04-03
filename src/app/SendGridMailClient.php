<?php

namespace App;

use SendGrid;
use SendGrid\Mail\Mail;

class SendGridMailClient
{
    /**
     * @var SendGrid
     */
    private $sendGrid;

    /**
     * SendGridMailClient constructor.
     * @param SendGrid $sendGrid
     */
    public function __construct(SendGrid $sendGrid)
    {
        $this->sendGrid = $sendGrid;
    }


    /**
     * @param $data
     * @return bool
     * @throws SendGrid\Mail\TypeException
     */
    public function sendGridMailTutorial($data)
    {
        $email = new Mail();
        $email->setFrom("test@example.com", "Test SendGridApi");
        foreach ($data as $i) {
            $item = DynamoMapper::unmarshalItem($i);
            if (!$item['email']) {
                continue;
            }
            $dayDiff = strtotime('now') - $item['created_at'];
            $dayOver = floor($dayDiff / (60 * 60 * 24));
            $email->setSubject("Email has been registered over: $dayOver days");
            $email->addTo($item['email']);
        }
        $email->addContent("text/plain", "Test SendGrid with lambda");

        try {
            $response = $this->sendGrid->send($email);
            if ($response->statusCode() === 200 || $response->statusCode() === 202) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            return false;
        }
    }
}
