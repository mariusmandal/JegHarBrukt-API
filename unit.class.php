<?php

class unit {
	
	public function __construct( $id=false ) {
		if( $id ) {
			$this->_load( $id );
		} else {
			throw new Exception('Invalid unit ID', 10);
		}
	}
	
	private function _load( $id ) {
		$this->ID = $id;
		$this->name = 'Web';
	}
}