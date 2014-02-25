<?php
// When loading category from user, unit ID may be another than current unit_id
// Loader will override unit_id to make it correct.
class category_meta {
	protected $table = 'jhb_category';
	
	public function __construct(){}
	
	public function register_hooks( $JHB ) {
		#HTTP_POST('category', $data)
		$JHB->register_action('POST', 'category', 'category', 'create');
		$JHB->register_action('DELETE', 'category', 'category', 'delete');
		$JHB->register_action('PUT', 'category', 'category', 'update');
		$JHB->register_action('GET', 'category', 'category', 'getCategory');
	}
}
class category extends category_meta {
	public $name;
	
	public function __construct( $JHB, $cat_id=false ) {
		if( $JHB ) 
			$this->_identify( $JHB );
		if( $cat_id ) {
			$this->_load( $cat_id );
		}
	}
	
	public function create( $data ) {
		if( empty( $data ) ) {
			throw new Exception('Missing data array upon creation of category', 300);
		}
		if( !isset( $data['name'] ) ) {
			throw new Exception('Missing name of new category', 301);
		}
		if( !is_string( $data['name'] ) ) {
			throw new Exception('Category name must be string', 302);
		}
		
		$existing_id = $this->_prevent_duplicate( $data['name'] );
		if( $existing_id ) {
			$this->_load( $existing_id );
		} else {
			$this->c_id = $this->_calc_next_id();
			$this->ID = $this->ID();
			$this->name = $data['name'];
			$sql = new mSQL_DB();
			$sql->insert($this->table)
				->set('u_id', 'unit_id', 'c_id', 'c_unique_id', 'c_name')
				->to($this->u_id, $this->unit_id, $this->c_id, $this->ID, $this->name)
				->process();
				;
			if( $sql->error ) {
				throw new Exception('Could not insert category because of SQL error ('.$sql->error.')', 306);
			}
		}
		
		return $this;
	}
	
	public function load( $id ) {
		$this->_load( $id );
	}
	
	public function ID() {
		if(!isset($this->unit_id)) {
			throw new Exception('Could not create category ID - missing Unit ID',304);
		}
		if(!isset($this->c_id)) {
			throw new Exception('Could not create category ID - missing Category ID for this Unit', 305);
		}
	
		return 'U'. $this->u_id .'UN'. $this->unit_id.'C'.$this->c_id;
	}
	
	private function _identify( $JHB ) {
		$auth = $JHB->get_auth();
		
		$this->u_id = $auth->user_id;
		$this->unit_id = $JHB->UNIT->ID;
	}
	
	private function _load( $id ) {
		$sql = new mSQL_DB();
		$sql->select('*')
			->from($this->table)
			->where('u_id','c_unique_id')
			->is($this->u_id, $id)
			->process();
		
		if( $sql->num_rows == 0 ) {
			$sql = new mSQL_DB();
			$sql->select('*')
				->from($this->table)
				->where('u_id','c_unique_id')
				->is(0, $id)
				->process();
			if( $sql->num_rows == 0 ) {			
				throw new Exception('Invalid category ID',303);
			}
		}
		$this->c_id = $sql->data['c_id'];
		$this->unit_id = $sql->data['unit_id'];
		$this->title = $sql->data['c_name'];
		$this->ID = $this->ID();
	}
	
	private function _calc_next_id() {
		$sql = new mSQL_DB();
		$sql->select('c_id')
			->from($this->table)
			->where('u_id')->is( $this->u_id)
			->order('c_id DESC')
			->limit(1)
			->process();
		if( $sql->num_rows == 0 )
			return 1;
		
		return ( (int) $sql->data['c_id'] + 1 );
	}
	
	private function _prevent_duplicate( $c_name ) {
		$sql = new mSQL_DB();
		$sql->select('c_unique_id')
			->from($this->table)
			->where('u_id','c_name')
			->is($this->u_id, $c_name)
			->process();

		if( $sql->num_rows > 0 ) {
			return $sql->data['c_unique_id'];
		}

		return false;
	}
}