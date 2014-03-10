<?php
/**
 * 子表达式 $ip + prev($ip)
 * @author seanxh
 */
class ChildExpression {
	/**
	 * 
	 * @var string
	 */
	private $_expression;
	
	/**
	 * @var stack 
	 */
	private $_postfix_notation;
	
	private $_rule_data;
	
	private $_postfix_expression; 
	
	public function  __construct($expression,$rule_data){
		$this->_expression = $expression;
		$this->_rule_data = $rule_data;
		$this->_postfix_expression = $this->infixToPostfix($this->_expression);
	}	
	
	public function preloadData($rule_data){
		foreach ($this->_postfix_expression as $operator){
			$arr  = $operator->preloadData();
			
			if( $arr ){
				$method = 'preload'.ucfirst($arr[0]);
				$params = array();
				for ($i=0;$i<count($arr[1]); $i++){
					$params[] = $operator->getData($arr[1][$i]);
				}
				call_user_func_array(array($rule_data,$method),$params);
			}
				
		}
	}
	
	/**
	 * 中缀转后缀表达式
	 * @example
	 * input : 1*2+(prev($abc,$ccc,abc,ccc)+30)/20*30
	 * Output: 
	 *  integer: 1
	 *  integer: 2
	 *  operator: *
	 *  function: prev($abc,$ccc,abc,ccc)
	 *  integer: 30
	 *  operator: +
	 *  integer: 20
	 *  operator: /
	 *  integer: 30
	 *  operator: *
	 *  operator: +
	 */
	public function infixToPostfix($str){
		
		$stack2 = array();
		
		$stack1 = array('#');
		
		$element = array();
		$type = null;
		$func_brackets = 0;
		
		for($i=0;$i<strlen($str);$i++){
			$char =  $str[$i];
			if( $char >= '0'  && $char <= '9' ){
				$element[] = $char;
				if($type == null)
					$type = Operator::INTEGER;
				continue;
			}else if( $char >= 'A' && $char <= 'z'){
				$element[] = $char;
				if($type == null)
					$type = Operator::FUNCTIONS;
				continue;
			}else if($char == '$'){
				$element[] = $char;
				if($type == null)
					$type = Operator::VARIABLE;
				continue;
			}else if($char == ',' || $char=='{' || $char == '}' || $char == ':'){
				$element[] = $char;
				continue;
			}
			
			switch ($char){
				case '+':
				case '-':
				case '*':
				case '/':
					if($type== Operator::FUNCTIONS){
						$element[]= $char;
						break;
					}
					
					if( !empty($element) ){
						$variable = implode('', $element);
						$stack2[] = new Operator($type, $variable);
						$element = array();
						$type = null;
					}
					
					if( $this->operatorCompare($char,end($stack1)) > 0 ){
						$stack1[] = $char;
					}else{
						while(count($stack1) > 0){
							$operator = end($stack1);
							if( $this->operatorCompare($char,$operator ) > 0 ){
								$stack1[] = $char;
								break;
							}else{
								$stack2[] = new Operator(Operator::OPERATOR, array_pop($stack1));
							}
						}
					}
					break;
				case '(':
					if($type==Operator::FUNCTIONS){
						$element[]= $char;
						$func_brackets ++;
					}else{
						$stack1[] = $char;
						
						if( !empty($element) ){
							$variable = implode('', $element);
							$stack2[] = new Operator($type, $variable);
							$element = array();
							$type = null;
						}
						
					}
					break;
					
				case ')':
					if($type==Operator::FUNCTIONS){
						
						$func_brackets --;
						if($func_brackets == 0){
							$element[]= $char;
							
							$variable = implode('', $element);
							$stack2[] = new Operator($type, $variable);
							
							$element = array();
							$type = null;
						}else{
							$element[]= $char;
						}
						
						
					}else{
						
						$variable = implode('', $element);
						$stack2[] = new Operator($type, $variable);
						$element = array();
						$type = null;
						
						while(count($stack1) > 0){
							$operator = array_pop($stack1);
							if( $operator == '(' ){
								break;
							}else{
								$stack2[] = new Operator(Operator::OPERATOR, $operator);
							}
						}
					}
					break;
					
			}
			
		}
		
		if( !empty($element) ){
				$stack2[] = new Operator($type, implode('', $element));
		}
		
		while(count($stack1) > 1){
// 			$operator = array_pop($stack1);
			$stack2[] = new Operator('operator', array_pop($stack1));;
		}
		
/* 		foreach ($stack2 as $v){
			echo $v."\n";
		} */
		
		return $stack2;
		
	}
	
	private function operatorCompare($operator1,$operator2){
		$operator = array('*'=>2,'/'=>2,'+'=>1,'-'=>1,'#'=>0);
		return $operator[$operator1] - $operator[$operator2];
	}
	
	public function calc($rule_data,$key){
		$postfix_stack = $this->_postfix_expression;
		$stack2 = array();
		
		while(count($postfix_stack) > 0){
			
			$operator = array_shift($postfix_stack);
			
			switch ($operator->type){
				case Operator::OPERATOR:
					$v2 = array_pop($stack2);
					$v1 = array_pop($stack2);
					switch ($operator->value){
						case '+':
							$value = $v1+$v2;
							break;
						case '-':
							$value = $v1-$v2;
							break;
						case '*':
							$value = $v1*$v2;
							break;
						case '/':
							$value = $v1/$v2;
							break;
					}
					array_push($stack2, $value);
					break;
				case Operator::FUNCTIONS:
				case Operator::INTEGER:
				case Operator::VARIABLE:					
					array_push($stack2, $operator->getValue($rule_data,$key) );
					break;
				default:
					break;
			}
		}
		
		if( count($stack2) == 1){
			return current($stack2);
		}else{
			throw new Exception('calc error');
			return false;
		}
		
	}
}
