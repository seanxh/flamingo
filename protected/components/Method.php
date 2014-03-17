<?php
class Method {
	
	private $_rule_data = null;
	
	private $_key = null;
	
	public function __construct($rule_data,$key){
		$this->_key = $key;
		$this->_rule_data =  $rule_data;
	}
	
	public function prev($column,$group,$cycle=1){
// 		var_dump( $column );
// 		var_dump( $group );
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
	
	public function arrays(){
		$stack =  func_get_args() ;
		$return_arr = array();
		foreach ($stack as $val){
			$arr_key  = 0;
			$arr_val = 0;
			if( is_int($val) || is_float($val)) {
					array_push($return_arr, $val);
			}else if(is_string($val)){
					$arr = explode(':', $val );
					if(count($arr ) > 1){
						$arr_key = $arr[0];
						$arr_val = $arr[1];
					}else{
						$arr_val = $val;
					}
			
					if( strstr($arr_key, '$') === 0 ){
						$arr_key = $this->_getVal($arr_key, $rule_data, $key);
					}
			
					if( strstr($arr_val, '$') === 0 ){
						$arr_val = $this->_getVal($arr_val, $rule_data, $key);
					}
					if( $arr_key === 0) {
						array_push($return_arr, $val);
					}else{
						$return_arr[$arr_key] = $arr_val;
					}
					
			}
		}
		
		return $return_arr;
				
	}

	/*
	 * 获取一个变量的值
	*/
	private function _getVal($val){
	
		$val = ltrim($val,'$');
		if(isset( $this->_rule_data[0][$this->_key][$val] )){
			return $this->_rule_data[0][$this->_key][$val];
		}
		return 0;
	
	}
}