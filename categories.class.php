<?php

class categories_meta {
	protected $table = 'jhb_category';
	
	public function __construct(){}
	
	public function register_hooks( $JHB ) {
		$JHB->register_action('GET', 'categories', 'categories', 'getcategories');
	}

}

class categories extends categories_meta {
	public $name;
	
	public function __construct( $JHB=false ) {
		if( $JHB ) 
			$this->_identify( $JHB );
	}
	
	private function _identify( $JHB ) {
		$auth = $JHB->get_auth();
		
		$this->u_id = $auth->user_id;
		$this->unit_id = $JHB->UNIT->ID;
	}
	
	public function get() {
		$this->_load();
	}
	
	private function _load() {
#		var_dump( $this );
	}
}