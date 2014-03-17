<?php

class  FunctionsStack{
	
	const OPERATOR = 'operator';//操作符
	const FUNCTIONS = 'function';//函数名
	const INTEGER = 'integer';//整型字符
	const VARIABLE = 'variable';//变量
	const BRACKET = 'bracket'; //括号
	const PENDING = 'pending';//未决
	const STRING = 'string';//字符串
	const ARRAYS = 'array';//数组（类似函数名)
	const ARRAYVAL = 'array_value';//数组值。变量
	
	public static $FUNC_PRELOAD = array(
			'prev' => array('group',1,array(2,1)), // 需要prev的函数名=>preload函数,需要取原函数的第几个参数作为preload的参数，如果为数组，可以指定默认值
			'next'=>array('count'),
	);
	
	private $_stack=array();
	
	public $PROCESS_CLASS = array(
		'Method',
		'AlertDeploy',
	) ;
	
	public function __construct(){
		
	}
	
	public function get(){
		return $this->_stack;
	}
	
	function push($type,$value){
		/* if($type == self::FUNCTIONS && $value == self::ARRAYS){
			$type = self::ARRAYS;
		} */
		$this->_stack[] = array($type,$value);
	}
	
	function pop(){
		return array_pop($this->_stack);
	}
	
	function getValue($rule_data,$key,$type='Method'){
		
		$method = new $type($rule_data, $key);
		
		/*
		 * bracket:)
		 * integer:9090
		 * integer:8080
		 * array:array
		 * bracket:(
		 */
		$function_stack = $this->_stack;
		
		$stack2 = array();
		while( count( $function_stack ) > 0){
			$element =  array_pop($function_stack);
			switch($element[0] ){
				case self::BRACKET:
					if ($element[1] == ')'){
						array_push($stack2, $element);
					}else{//碰到(，把函数栈中的push出来，计算重新入栈
						$func_ele_stack = array();
						while( count($stack2) >0){
							$ele = array_pop($stack2);
							if( $ele[1] !=  ')'  ){
								array_push($func_ele_stack, $ele);
							}else{
								break;
							}
						}
						switch ($func_ele_stack[0][0]){
							case self::FUNCTIONS:
								$function_name = array_shift($func_ele_stack);
								$function_name = $function_name[1];
								$params = array();
								foreach ($func_ele_stack as $parameter){
									$params[] = $parameter[1];
								}
								
								$val = call_user_func_array(array($method,$function_name), $params);
								
								if( is_int($val) || is_float($val)){
									array_push($stack2 , array(FunctionsStack::INTEGER,$val) );
								}else if(is_string($val)){
									array_push($stack2 , array(FunctionsStack::STRING,$val) );
								}else if(is_array($val)){
									array_push($stack2 , array(FunctionsStack::ARRAYVAL,$val) );
								}
								
								break;
							default:
								break;
						}
						
					}
					break;
				case self::INTEGER:
				case self::STRING:
				case self::VARIABLE:
				case self::FUNCTIONS:
				case self::ARRAYS:
					array_push($stack2, $element);
					break;
			}
			
		}
		
		if( count($stack2) == 1){
			$current_satck  = current($stack2);;
			if( is_array($current_satck) ){
					return $current_satck[1];
			}
			return $current_satck;
		}else{
			throw new Exception('calc error');
			return false;
		}
		
	}
	
}