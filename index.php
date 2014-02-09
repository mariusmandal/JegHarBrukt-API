<?php
require_once('config.php');
require_once('vendor/autoload.php');

try {
	$mAPI = new mAPI_ACCESS( 'test', 'test');
} catch( Exception $e ) {
	die( 'API ACCESS NOT GRANTED: '. $e->getMessage() . ' ('.$e->getCode().')' );
}

var_dump($mAPI);