<?php

class user {
	protected $u_id;
	protected $api_got_access;
	protected $table = 'jhb_user';
	
	public function __construct( $JHB=false, $id=false ) {
		if( $JHB ) {
			$this->_identify( $JHB );
		}
		if( $id ) { 
			$this->_load( $id );
		}
	}

	public function register_hooks( $JHB ) {
		#HTTP_POST('category', $data)
		$JHB->register_action('POST', 'user', 'user', 'create');
		$JHB->register_action('DELETE', 'user', 'user', 'delete');
		$JHB->register_action('PUT', 'user', 'user', 'update');
		$JHB->register_action('GET', 'user', 'user', 'get');
		$JHB->register_action('GET', 'user-available', 'user', 'available');
	}
	
	public function available( $username ) {
		if( empty( $username ) ) {
			throw new Exception('Cannot check empty username', 402);
		}
		$sql = new mSQL_DB();
		$sql->select('u_id')
			->from('jhb_user')
			->where('u_username')->is($username)
			->process();
		
		if( $sql->num_rows > 0 ) {
			return false;
		}
		return true;
	}
	
	public function create( $data ) {
		if( !isset( $data['username'] ) || empty( $data['username'] ) ) {
			throw new Exception('Cannot create user without username', 408);
		}

		if( !$this->available( $data['username'] ) ) {
			throw new Exception('Username already exists',406);
		}

		if( !isset( $data['email'] ) || empty( $data['email'] ) ) {
			throw new Exception('Cannot create user without valid e-mail address', 403);
		}
		if( !isset( $data['password'] ) || empty( $data['password'] ) ) {
			throw new Exception('Cannot create user without password', 404);
		}
		
		$fields = array('username'=>null,
						'email'=>null,
						'password'=>null,
						'salt'=>null,
						'first_name'=>null,
						'last_name'=>null,
						'face_id'=>0);
						
		$data['salt'] = $this->_salt();
		$data['reg_time'] = $this->_reg_time();
		$data['password'] = $this->_hash( $data );
		
		$insert = array_merge($fields, $data);
		
		$sql = new mSQL_DB();
		$sql->insert($this->table)
			->set('u_username',
				  'u_email',
				  'u_password',
				  'u_first_name',
				  'u_last_name',
				  'face_id',
				  'u_pwd_salt',
				  'u_reg_time')
			->to($insert['username'],
				 $insert['email'],
				 $insert['password'],
				 $insert['first_name'],
				 $insert['last_name'],
				 $insert['face_id'],
				 $insert['salt'],
				 $insert['reg_time'])
			->process();
		
		if( $sql->error ) {
			throw new Exception('Unidentified SQL error on user create',407);
		}
		$this->_load( $sql->id );
		unset( $data );
		unset( $sql );

		return $this;
	}
	
	public function login( $username, $password ) {
		$sql = new mSQL_DB();
		$sql->select('u_id','u_pwd_salt','u_reg_time','u_password')
			->from($this->table)
			->where('u_username')
			->is( $username )
			->process();

		if( $sql->num_rows == 1 ) {
			$data['salt'] 		= $sql->data['u_pwd_salt'];
			$data['reg_time'] 	= $sql->data['u_reg_time'];
			$data['password']	= $password;
			$enteredHash = $this->_hash( $data );
			
			if( $enteredHash == $sql->data['u_password'] ) {
				$this->_load( $sql->data['u_id'] );
				$confirmation = sha1( $sql->data['u_password'] . $data['salt'] );
				unset( $data );
				unset( $sql );
				return $confirmation;
			}
			throw new Exception('Wrong username/password', 410);
		}
		throw new Exception('Username does not exist', 409);
	}
	
	public function restore_session( $u_id, $hash ) {
		$sql = new mSQL_DB();
		$sql->manual("SELECT `u_id` FROM `jhb_user` WHERE SHA1(CONCAT(`u_password`,`u_pwd_salt`)) = '$hash' AND `u_id` = '$u_id'")
			->process();
		
		if( $sql->num_rows == 1 ) {
			if( $sql->data['u_id'] == $u_id ) {
				return $this;
			}
		}
		
		throw new Exception('Could not restore user session. Wrong ID/Hash-combination', 411);
	}
	
	private function _identify( $JHB ) {
		$auth = $JHB->get_auth();
		
		$this->u_id = $auth->user_id;
		$this->unit_id = $JHB->UNIT->ID;
	}
	
	private function _salt() {
		$length = rand(2,20);
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}
	
	private function _reg_time() {
		return date('Y-m-d H:i:s');
	}
	
	private function _hash( $data ) {
		if( !isset( $data['password'] ) || empty( $data['password'] ) 
		 || !isset( $data['salt'] ) || empty( $data['salt'] ) 
		 || !isset( $data['reg_time'] ) || empty( $data['reg_time'] )
		  ) {
		  	unset( $data );
			throw new Exception('Password hash error. Could not generate password hash', 405);
		}
		
		return sha1( $data['salt'] . JHB_PWD_PEPPER . $data['password'] . substr($data['reg_time'],5) );
	}
	
	private function _load( $id ) {
		if( (int) $id == 0 ) {
			throw new Exception('Could not load user without ID', 400);
		}
		$sql = new mSQL_DB();
		$sql->select('u_id','u_username','u_email','u_first_name','u_last_name','face_id','DATE_FORMAT(u_reg_time,\'%Y-%m-%d\') AS u_reg_time')
			->from('jhb_user')
			->where( 'u_id' )->is( $id )
			->process();
		
		if( $sql->num_rows == 0 ) {
			throw new Exception('Could not find user with ID '. $id, 401);
		}
		
		$this->ID = $sql->data['u_id'];
		$this->username = $sql->data['u_username'];
		$this->email = $sql->data['u_email'];
		$this->face_id = $sql->data['face_id'];
		$this->reg_time = $sql->data['u_reg_time'];
		$this->name = new stdClass();
		$this->name->first = $sql->data['u_first_name'];
		$this->name->last = $sql->data['u_last_name'];
		$this->name->full = $this->name->first .' '. $this->name->last;
	}
}