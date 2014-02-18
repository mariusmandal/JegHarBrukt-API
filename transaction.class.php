<?php
class transaction {
	private $table = 'jhb_transaction';
	public $amount;
	public $category;
	public $description;
	
	
	public function __construct( $JHB, $trans_id=false ) {
		$this->_identify( $JHB );
		if( $trans_id ) {
			$this->_load( $trans_id );
		}
	}
	
	public function register_hooks( $JHB ) {
		#HTTP_POST('category', $data)
		$JHB->register_action('POST', 'transaction', 'transaction', 'create');
		$JHB->register_action('DELETE', 'transaction', 'transaction', 'delete');
		$JHB->register_action('PUT', 'transaction', 'transaction', 'update');
		$JHB->register_action('GET', 'transaction', 'transaction', 'getCategory');
	}
	
	private function _load( $trans_id ) {
		
	}

	public function create( $data ) {
		if( empty( $data ) ) {
			throw new Exception('Missing data array upon creation of transaction', 400);
		}
		if( !isset( $data['amount'] ) ) {
			throw new Exception('Missing transaction amount', 401);
		}
		if( !is_string( $data['category'] ) ) {
			throw new Exception('Missing transaction category', 402);
		}
		$this->c_id 		= $data['category'];
		
		$this->t_id 		= $this->_calc_next_id();
		$this->ID 			= $this->ID();
		$this->amount 		= $data['amount'];
		$this->description 	= $data['description'];

		$sql = new mSQL_DB();
		$sql->insert($this->table)
			->set('u_id', 'unit_id', 'c_id', 't_id', 't_unique_id', 't_amount', 't_description')
			->to($this->u_id, $this->unit_id, $this->c_id, $this->t_id, $this->ID, $this->amount, $this->description)
			->process();
			;
		if( $sql->error ) {
			throw new Exception('Could not insert transaction because of SQL error ('.$sql->error.')', 406);
		}
	}

	private function _calc_next_id() {
		$sql = new mSQL_DB();
		$sql->select('t_id')
			->from($this->table)
			->where('u_id')->is($this->u_id)
			->order('t_id DESC')
			->limit(1)
			->process();
		if( $sql->num_rows == 0 )
			return 1;
		
		return ( (int) $sql->data['t_id'] + 1 );
	}

	public function ID() {
		if(!isset($this->unit_id)) {
			throw new Exception('Could not create transaction ID - missing Unit ID',304);
		}
		if(!isset($this->t_id)) {
			throw new Exception('Could not create transaction ID - missing Transaction ID for this Unit', 305);
		}
	
		return 'U'. $this->u_id .'UN'. $this->unit_id.'T'.$this->t_id;
	}

	
/*
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
*/
	
	private function _identify( $JHB ) {
		$auth = $JHB->get_auth();
		
		$this->u_id = $auth->user_id;
		$this->unit_id = $JHB->UNIT->ID;
	}
}

?>