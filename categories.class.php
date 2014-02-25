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
	public $categories = array();
	
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
		$this->categories = array('system'=>array(), 'user'=>array());
		$this->_load();
		return $this->categories;
	}
	
	private function _load() {
		$this->_load_from_db( 'user', $this->u_id );
		$this->_load_from_db( 'system',  0 );
	}
	
	private function _load_from_db( $group,  $u_id ) {
		$sql = new mSQL_DB();
		$sql->select('*')
			->from( $this->table )
			->where('u_id')->is( $u_id )
			->order( 'c_name ASC' )
			->process();
		while( $c = $sql->fetch() ) {
			$category = new category( false, false );
			$category->u_id = $this->u_id;
			$category->unit_id = $this->unit_id;
			$category->load( $c['c_unique_id'] );
			$this->categories[ $group ][] = $category;
		}

	}
}