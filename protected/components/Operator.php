<?php
class Operator{
	
	private $_type;
	private $_value;
	const OPERATOR = 'operator';
	const FUNCTIONS = 'function';
	const INTEGER = 'integer';
	const VARIABLE = 'variable';
	
	const PENDING = 'pending';
	const STRING = 'string';
	
	private $_func_stack;
	
	function __construct($type,$value){
		$this->_type = $type;
		$this->_value = $value;
		
		$this->_func_stack = $this->analyseFuncStack($this->_value);
	}
	
	function __toString(){
		return strtolower($this->_type ) . ': ' . $this->_value;
	}
	function getValue() {
		switch ($this->_type) {
			case self::FUNCTIONS :
				return 10;
				break;
			case self::VARIABLE :
				return 20;
				break;
			case self::INTEGER:
				return $this->_value;
				break;
			case self::OPERATOR:
				return $this->_value;
				break;
		}
	}
	
	function preloadData() {
		
		if ($this->_type == self::FUNCTIONS) {
			$func_stack = $this->_func_stack->get ();
			$arr = $this->checkIsNeedPreload ( $func_stack );
			if ( $arr )
				return $arr;
			
		}
		
		return false;
	}
	
	
	function checkIsNeedPreload($arr){
		$arr2 = array();
		$flag = false;
		while( $stack = array_pop($arr) ){
			if($stack['0'] == FunctionsStack::FUNCTIONS && array_key_exists($stack['1'], Calc::$FUNC_PRELOAD) ){
				$flag = true;
				break;
			}
			array_push($arr2, $stack);
		}

		if( !$flag ) return false; 	
		
		$func_name = $stack[1];
			
		$bracket = 1;
			
		$params = array();
			
		$elements = array();
		while( $stack = array_pop($arr2)){
			if ( $stack[0] == FunctionsStack::BRACKET ){
				if( $stack[1] == ')' ){
					$bracket  -- ;
					if($bracket == 0){
						break;
					}else if($bracket == 1) {
						$elements[] = ')';
						$params[] = $elements;
						$elements = array();
					}else{
						$elements[] = ')';
					}
		
				}else if($stack[1] == '('){
					$elements[] = '(';
					$bracket ++;
				}
					
			}else if( $stack[0] == FunctionsStack::FUNCTIONS  || $stack[0] == FunctionsStack::ARRAYS){
				$elements[] = $stack[1];
			}else{
				if( $bracket > 1){
					$elements[] = $stack[1];
				}else{
					$params[] = $stack[1];
				}
					
			}
		
		}
		
		$preload_params = array();
		for($i=1;$i< count(Calc::$FUNC_PRELOAD[$func_name]); $i++ ){
			$preload_params[] = $params[Calc::$FUNC_PRELOAD[$func_name][$i]];
		}
		
		return
		array(
		Calc::$FUNC_PRELOAD[$func_name][0],
		$preload_params
		);
	}
	
	function getData($variable){
		
		if( is_array($variable) ){
			unset($variable[0]);
			unset($variable[1]);
			array_pop($variable);
			$arr  = array();
			foreach ($variable as $v){
				$arr[] = $v;
			}
			
			return $arr;
		}else{
			return $variable;
		}
		
	}
		
	function analyseFuncStack($str){
		$func_stack = new FunctionsStack();
		
		$str = str_replace('{', 'array(', $str);
		$str = str_replace('}', ')', $str);
		
		$element = array();
		$type = null;
		
		for($i=0;$i<strlen($str);$i++){
			$char =  $str[$i];
			if( $char >= '0'  && $char <= '9' ){
				$element[] = $char;
				if($type == null)
					$type = FunctionsStack::INTEGER;
				continue;
			}else if( $char >= 'A' && $char <= 'z'){
				$element[] = $char;
				if($type == null)
					$type = FunctionsStack::PENDING;
				continue;
			}else if($char == '$'){
				$element[] = $char;
				if($type == null)
					$type = FunctionsStack::VARIABLE;
				continue;
			}else if($char == ','){
// 				$element[] = $char;
				if(!empty($element)){
					if($type == FunctionsStack::PENDING){
						$type = FunctionsStack::STRING;
					}
					$func_stack->push( $type,implode('', $element) );
					$element = array();
					$type = null;
				}
				continue;
			}else if($char == ':'){
				$element[] = $char;
				continue;
			}
			
			switch ($char){
				case '(':
					$type = FunctionsStack::FUNCTIONS;
					if(!empty($element)){
						$func_stack->push( FunctionsStack::BRACKET, '(' );
						$func_stack->push($type ,implode('', $element) );
						$element = array();
						$type = null;
					}
					break;
				case ')':
					if($type == FunctionsStack::PENDING){
						$type = FunctionsStack::STRING;
					}
					if(!empty($element)){
						$func_stack->push($type ,implode('', $element) );
						$func_stack->push( FunctionsStack::BRACKET, ')' );
						$element = array();
						$type = null;
					}else{
						$stack[] = ')';
					}
					break;
			}
			
		}

		/* foreach ($func_stack->get() as $k=>$v){
			echo $v[0].':' . $v[1]."\n";
		} */
		
		return $func_stack;
	}
	
}