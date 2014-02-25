<?php

class categories_meta {
	protected $table = 'jhb_category';
	
	public function __construct(){}
	
	public function register_hooks( $JHB ) {
		$JHB->register_action('GET', 'categories', 'categories', 'get');
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
		
		if( !$auth ){
			throw new Exception('Not authorized');
		}		
		$this->u_id = $auth->user_id;
		$this->unit_id = $JHB->UNIT->ID;
	}
	
	public function get() {
		$this->_load();
	}
	
	private function _load() {
		$sql = new mSQL_DB();
		$sql->select('*')
			->from( $this->table )
			->order( 'c_name ASC' )
			->process();
		while( $c = $sql->fetch() ) {
			$category = new category( false, $c['c_unique_id'] );
		}
#		var_dump( $this );
	}
}