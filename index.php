<?php
$_APIDATA = new stdClass();

$_APIDATA->api = new stdClass();
$_APIDATA->api->key = 'test';
$_APIDATA->api->secret = 'test';

$_APIDATA->unit = 1;

$_APIDATA->user = new stdClass();
$_APIDATA->user->ID = 1;
$_APIDATA->user->token = 'test';

/// TEST 1 - OPPRETT KATEGORI
	$_APIDATA->request = new stdClass();
	$_APIDATA->request->method = 'POST';
	$_APIDATA->request->object = 'category';
	$_APIDATA->request->data	= array('name' => 'Category 3');

/// TEST 2 - OPPRETT TRANSAKSJON I KATEGORI 1 (U1,UN1,C1)

	$_APIDATA->request = new stdClass();
	$_APIDATA->request->method = 'POST';
	$_APIDATA->request->object = 'transaction';
	$_APIDATA->request->data	= array('description' => 'Just for fun', 'amount' => 100, 'category' => 'U1UN1C1');


/// JHB API

require_once('config.php');
require_once('vendor/autoload.php');
// 
require_once('unit.class.php');

// CREATE MAIN APP INSTANCE AND AUTHENTICATE
	require_once('jegharbrukt.class.php');
	$JHB = new JHB();
	try {
		$JHB->authenticate( $_APIDATA->api->key, $_APIDATA->api->secret );
	} catch( Exception $e ) {
		APIdie( $e );
	}
	
	try {
		$JHB->setUnit( $_APIDATA->unit );
	} catch( Exception $e ) {
		APIdie( $e );
	}

// IDENTIFY ACTIVE USER AND AUTHORIZE API
	require_once('user.class.php');
	try {
		$USER = new user( $_APIDATA->user->ID );
	} catch( Exception $e ) {
		APIdie( $e );
	}
	
	try {
		$JHB->authorize( $USER, $_APIDATA->user->token );
	} catch( Exception $e ) {
		APIdie( $e );
	}

// REQUIRE DEPENDENCIES (AND REGISTER HOOKS)
	// CATEGORY
	require_once('category.class.php');
	$category = new category( $JHB );
	$category->register_hooks( $JHB );

	// TRANSACTION
	require_once('transaction.class.php');
	$transaction = new transaction( $JHB );
	$transaction->register_hooks( $JHB );


// IF SURVIVED ALL THE WAY HERE, TIME TO PROCESS REQUEST
	try {
		$JHB->{$_APIDATA->request->method}( $_APIDATA->request->object, $_APIDATA->request->data );
	} catch( Exception $e ) {
		APIdie( $e );
	}
	
die( json_encode(false) );



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