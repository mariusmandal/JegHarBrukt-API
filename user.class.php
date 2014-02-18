<?php

class user {
	protected $u_id;
	protected $api_got_access;
	
	public function __construct( $id=false ) {
		if( $id ) { 
			$this->_load( $id );
		}
	}
	
	private function _load( $id ) {
		if( (int) $id == 0 ) {
			throw new Exception('Could not load user without ID', 400);
		}
		$sql = new mSQL_DB();
		$sql->select('*')
			->from('jhb_user')
			->where( 'u_id' )->is( $id )
			->process();
		
		if( $sql->num_rows == 0 ) {
			throw new Exception('Could not find user with ID '. $id, 401);
		}
		
		$this->ID = $sql->data['u_id'];
		$this->name = new stdClass();
		$this->name->first = $sql->data['u_first_name'];
		$this->name->last = $sql->data['u_last_name'];
		$this->name->full = $this->name->first .' '. $this->name->last;
	}
}