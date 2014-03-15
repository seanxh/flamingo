<?php
class Method {
	
	private $_rule_data = null;
	
	private $_key = null;
	
	public function __construct($rule_data,$key){
		$this->_key = $key;
		$this->_rule_data =  $rule_data;
	}
	
	public function prev($column,$group,$cycle=1){
// 		var_dump($this->_rule_data[$cycle]);
// 		var_dump($this->_key);
		if( isset( $this->_rule_data[ $cycle ] [ $this->_key ] [ $column ]) )
			return $this->_rule_data[ $cycle ] [ $this->_key ] [ $column ];
		return 0;
	}
	
	public function str_prev_hour($n=1){
		return date('Y-m-d H:i:s',time() - 86400*$n);
	}
	
	public function count(){
		return count($this->_rule_data[0]);
	}
	
	public function prevHour($column,$group,$cycle=1){
		
	}
	
}