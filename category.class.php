<?php

class category {
	public $name;
	
	public function __construct( $id=false ) {
		if( $id ) {
			$this->_load( $id );
		}
	}
	
	
	public function register_hooks( $JHB ) {
		#HTTP_POST('category', $data)
		$JHB->register_action('POST', 'category', 'category', 'create');
		$JHB->register_action('DELETE', 'category', 'category', 'delete');
		$JHB->register_action('PUT', 'category', 'category', 'update');
		$JHB->register_action('GET', 'category', 'category', 'getCategory');
		$JHB->register_action('GET', 'categories', 'category', 'getCategories');
	}
	
	public function create( $data ) {
		if( empty( $data ) ) {
			throw new Exception('Missing data array upon creation of category', 300);
		}
		if( !isset( $data['name'] ) ) {
			throw new Exception('Missing name of new category', 301);
		}
		echo 'Creating category';
		echo 'Hvordan få inn USER ID, UNIT ID ?';
	}
}