<?php

class JHB extends API {
	
}

class API {
	protected $API;
	
	public function __construct( ) {
	}
	
	public function authenticate( $key, $secret ) {
		try {
			$this->API = new mAPI_ACCESS( $key, $secret );
		} catch( Exception $e ) {
			throw $e;
		}
	}
	
	public function authorize( $user, $token ) {
		if( 'user' != get_class( $user ) ) {
			throw new Exception('Cannot authorize without user object', 30);
		}
		if( empty( $token ) ) {
			throw new Exception('Cannot authorize without user token', 31);
		}
		try{
			$this->API->authorize( $user->ID, $token );
		} catch (Exception $e ) {
			throw $e;
		}
	}

	public function get_auth() {
		return $this->API->get_auth();
	}
	
	public function setUnit( $unit ) {
		try{
			$this->UNIT = new unit( $unit );
		} catch( Exception $e ) {
			throw $e;
		}
	}
	
	public function POST( $object, $data ) {
		if( !$this->API->is_authenticated() ) {
			throw new Exception('Api access not granted!', 20); 
		}
		if( !$this->API->has_writeaccess() ) {
			throw new Exception('Api not granted write access', 21);
		}
		
		if( 'unit' != get_class( $this->UNIT ) ) {
			throw new Exception('Api not allowed write access without unit ID', 22);
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
		
		$action_object = new $class( $this );
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