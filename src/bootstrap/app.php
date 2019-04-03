<?php

$dotenv = Dotenv\Dotenv::create(dirname(__DIR__));
$dotenv->load();

require 'constant.php';
date_default_timezone_set('UTC');
