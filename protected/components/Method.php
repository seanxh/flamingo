<?php
class Method {
	
	private $_rule_data = null;
	
	private $_key = null;
	
	public function __construct($rule_data,$key){
		$this->_key = $key;
		$this->_rule_data =  $rule_data;
	}
	
	public function prev($column,$group,$cycle=1){
		return $this->_rule_data[ $cycle ] [ $this->_key ] [ $column ];
	}
}