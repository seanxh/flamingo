<?php
class Operator{
	
	public $type;
	public $value;
	const OPERATOR = 'operator';
	const FUNCTIONS = 'function';
	const INTEGER = 'integer';
	const VARIABLE = 'variable';
	
	const PENDING = 'pending';
	const STRING = 'string';
	
	/**
	 * @var FunctionsStack
	 */
	private $_func_stack = null;
	
	function __construct($type,$value){
		$this->type = $type;
		$this->value = $value;
		
		if($type == self::FUNCTIONS){
			$this->_func_stack = $this->analyseFuncStack($this->value);
		}
	}
	
	function __toString(){
		return strtolower($this->type ) . ': ' . $this->value;
	}
	
	
	function getValue($rule_data,$key) {
		switch ($this->type) {
			case self::FUNCTIONS :
				var_dump($this->_func_stack->get());
				return 10;
				break;
			case self::VARIABLE :
				$k = ltrim( $this->value , '$');
				return $rule_data[0][$key][$k];				
				break;
			case self::INTEGER:
				return $this->value;
				break;
			case self::OPERATOR:
				return $this->value;
				break;
		}
	}
	
	function preloadData() {
	
		if ($this->type == self::FUNCTIONS) {
			$func_stack = $this->_func_stack->get ();
			$arr = $this->checkIsNeedPreload ( $func_stack );
			if ( $arr )
				return $arr;
			
		}
		
		return false;
	}
	
	//检查是否需要preload数据
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
					if($bracket == 0){//bracket为0，代表已经找到该函数的尾
						break;
					}else if($bracket == 1) {//为1则代表还有,把之前的func() push进params
						$elements[] = $stack;
						$params[] = $elements;
						$elements = array();
					}else{
						$elements[] = $stack;
					}
		
				}else if($stack[1] == '('){//有子调用 ，array()
					$elements[] = $stack;
					$bracket ++;
				}
					
			}else if( $stack[0] == FunctionsStack::FUNCTIONS  || $stack[0] == FunctionsStack::ARRAYS){//是数组或函数名
				$elements[] = $stack;
			}else{//其它情况，依次PUSH进调用栈中
				if( $bracket > 1){//如果还有括号
					$elements[] = $stack;
				}else{//已经没有括号了
					$params[] = $stack;
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
		
		if(is_array($variable) && !empty($variable)){
			if( is_array($variable) ){
				array_shift($variable);
				array_pop($variable);
				
				if ( $variable[0][0] == FunctionsStack::ARRAYS ){
					array_shift($variable);
					$arr  = array();
					foreach ($variable as $v){
						$arr[] = $v[1];
					}
					return $arr;
				}
					
				return null;
			}else{
				return $variable;
			}
		}else if (is_array($variable)){
				return array();
		}else{
			return 1;
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