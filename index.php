<?php
require_once('config.php');
require_once('vendor/autoload.php');

// 
require_once('unit.class.php');

// CREATE MAIN APP INSTANCE AND AUTHENTICATE
	require_once('jegharbrukt.class.php');
	$JHB = new JHB();
	try {
		$JHB->authenticate( 'test', 'test' );
	} catch( Exception $e ) {
		APIdie( $e );
	}
	
	try {
		$JHB->setUnit( 1 );
	} catch( Exception $e ) {
		APIdie( $e );
	}

// REQUIRE DEPENDENCIES (AND REGISTER HOOKS)
	require_once('category.class.php');
	$category = new category();
	$category->register_hooks( $JHB );

$USER = new user();

$METHOD = 'POST';
$OBJECT = 'category';
$DATA 	= array('name'=>'tast');
try {
	$JHB->$METHOD( $OBJECT, $DATA );
} catch( Exception $e ) {
	APIdie( $e );
}

/*

echo 'connected to API';

// ADD NEW CATEGORY
$category = new category();
try {
	$category->create( $name );
} catch( Exception $e ) {
	die( 'ERROR' );
}
*/

function APIdie( $e ) {
	$error = new stdClass();
	$error->status = 400;
	$error->developerMessage = 'API ERROR: '. $e->getMessage() . ' ('.$e->getCode().')' ;
	$error->errorCode = $e->getCode();
	if( $error->errorCode < 100 ) {
		$error->userMessage = 'Could not connect to API';
	} elseif( $error->errorCode > 100 ) {
		$error->userMessage = 'API did not retrieve mandatory data';
	}
	
	die( json_encode( $error ) );
}