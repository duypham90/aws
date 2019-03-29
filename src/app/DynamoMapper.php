<?php

namespace App;

use Aws\DynamoDb\Marshaler;

/**
 * @method static unmarshalItem(array $data, $mapAsObject = false)
 * @method static marshalJson($json)
 * @see Marshaler
 */
class DynamoMapper
{
    // Hold an instance of the class
    private static $instance;

    // prevent creating multiple instances due to "private" constructor
    private function __construct()
    {
    }

    // prevent the instance from being cloned
    private function __clone()
    {
    }

    // prevent from being unserialized
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * @return Marshaler
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Marshaler;
        }

        return self::$instance;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::getInstance()->$name(...$arguments);
    }
}