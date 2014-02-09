<?php

class JHB {
	protected $authenticated = false;
	protected $API;
	
	public function __construct( ) {
	}
	
	public function authenticate( $key, $secret ) {
		try {
			$this->API = new mAPI_ACCESS( $key, $secret );
		} catch( Exception $e ) {
			throw $e;
		}
		$this->authenticated = true;
		$this->API->writeaccess = true;
	}
	
	public function setUnit( $unit ) {
		try{
			$this->UNIT = new unit( $unit );
		} catch( Exception $e ) {
			throw $e;
		}
	}
	
	public function POST( $object, $data ) {
		if( !$this->authenticated ) {
			throw new Exception('Api access not granted!', 20); 
		}
		if( !$this->API->writeaccess ) {
			throw new Exception('You are not granted write access', 21);
		}
		
		if( empty( $object ) ) {
			throw new Exception('Cannot work on empty object', 100);
		}
		if( empty( $data ) || 'array' != gettype( $data ) ) {
			throw new Exception('Data-array is mandatory when creating '. $object, 101);
		}
		
		$this->_execute('POST', $object, false, $data );
	}
	
	private function _execute( $method, $action, $id=false, $data=false ) {
		if( !isset( $this->hooks[$method][ $action ] ) ) {
			throw new Exception('Requested function is not registered in API ('. $method .'::'. $action.')', 105);
		}
		
		$function	= $this->hooks[ $method ][ $action ]['function'];
		$class 		= $this->hooks[ $method ][ $action ]['class'];
		
		$action_object = new $class();
		$action_object->$function( $data );
	}
	
	public function register_action( $method, $action, $class, $function ) {
		if( empty( $method ) ) {
			throw new Exception('Method is required when registering hook',102);
		}
		if( empty( $class ) ) {
			throw new Exception('Class is required when registering hook',103);
		}
		if( empty( $action ) ) {
			throw new Exception('Action name is required when registering hook',104);
		}
		if( empty( $function ) ) {
			throw new Exception('Function name is required when registering hook',104);
		}

		$this->hooks[ strtoupper($method) ][$action] = array( 'class' => $class, 'function' => $function);
	}
}