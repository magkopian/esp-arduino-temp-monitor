<?php
/*
* Copyright (c) 2017 Manolis Agkopian
* See the file LICENSE for copying permission.
*/

define('MYSQL_USER', '[username]');
define('MYSQL_PASSWD', '[password]');
define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_DATABASE', 'sensors');

$client_ids = [1];
$client_keys = [1 => '[client key]'];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        $data = json_decode(file_get_contents('php://input'), true);

        $temperature = 0;
        if ( isset($data['temperature']) ) {
                $temperature = (int) $data['temperature'];
        }

        $humidity = 0;
        if ( isset($data['humidity']) ) {
                $humidity = (int) $data['humidity'];
        }

        $client_id = 0;
        if ( isset($data['client_id']) ) {
                $client_id = (int) $data['client_id'];
        }

        // Check client id and client key
        if ( !in_array($client_id, $client_ids) || !isset($data['client_key']) || $data['client_key'] != $client_keys[$client_id] ) {
                die();
        }

        $time = date(DATE_RFC2822);
        $time = date("d-m-Y H:i");
        log_data($client_id, $temperature, $humidity);

		// Send current time to the client
        echo $time;
}

/**
* Logs the temperature and humidity into the database
*/
function log_data ($client_id, $temperature, $humidity) {
        try {
                $dbh = new PDO('mysql:dbname=' . MYSQL_DATABASE . ';host=' . MYSQL_HOST, MYSQL_USER, MYSQL_PASSWD);
                $sth1 = $dbh->prepare('INSERT INTO `temperature` (`client_id`, `value`) VALUES (:client_id, :temperature)');
                $sth2 = $dbh->prepare('INSERT INTO `humidity` (`client_id`, `value`) VALUES (:client_id, :humidity)');
                $sth1->execute([':client_id' => $client_id, ':temperature' => $temperature]);
                $sth2->execute([':client_id' => $client_id, ':humidity' => $humidity]);
        }
        catch ( PDOException $e ) {
                echo 'Connection failed: ' . $e->getMessage();
        }
}

