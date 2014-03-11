<?php
class Method {
	
	private $_rule_data = null;
	
	private $_key = null;
	
	public function __construct($rule_data,$key){
		$this->_key = $key;
		$this->_rule_data =  $rule_data;
	}
	
	public function prev($column,$group,$cycle=1){
// 		var_dump($column);
// 		var_dump($cycle);
		if( isset( $this->_rule_data[ $cycle ] [ $this->_key ] [ $column ]) )
			return $this->_rule_data[ $cycle ] [ $this->_key ] [ $column ];
		return 0;
	}
	
	public function prevHour($column,$group,$cycle=1){
		
	}
}