<?php
class transaction {
	public $amount;
	public $category;
	public $description;
	
	
	public function __construct( $id = false) {
		if( is_int( $id ) ) {
			$this->_read( $id );
		}
	}
	
	private function _read() {
		
	}
	
	public function create($amount, $description, $category_id) {
		if( !is_int( $amount ) || $amount == 0 ) {
			throw Exception('Amount is not a number. Given ' . gettype( $description ) );
		}
		if( !is_string( $description) ) {
			throw Exception('Description is not a string. Given '. gettype( $description ) );
		}
		if( !is_int( $category_id ) || $category_id == 0 ) {
			throw Exception('Category id is not a number. Given '. gettype( $description ) );
		}
		
		$this->_create( $amount, $description, $category_id);
	}
	
	private function $this->_create( $amount, $description, $category_id ) {
		$sql = new SQL();
		$sql->insert('jhb_transaction')
			->set('amount','description','category_id')
	}
}

?>