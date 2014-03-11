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
	
	/**
	 * @var RuleData
	 */
	private $_rule_data;
	
	private $_postfix_expression; 
	
	public function  __construct($expression,$rule_data){
		$this->_expression = $expression;
		$this->_rule_data = $rule_data;
		$this->_postfix_expression = $this->infixToPostfix($this->_expression);
	}	
	
	public function __toString(){
		return $this->_expression;
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
	 * @param string $str 中缀表达式
	 * @return Array(Operator)
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
		
		for($i=0;$i<strlen($str);$i++){//挨个遍历表达式
			$char =  $str[$i];
			if( $char >= '0'  && $char <= '9' ){
				$element[] = $char;
				if($type == null)//如果以数字开头，且之前没有被定义类型，则为一个整型数字的开头
					$type = Operator::INTEGER;
				continue;
			}else if( ($char >= 'A' && $char<='Z') || ($char>='a' && $char <= 'z')){
				$element[] = $char;
				if($type == null)//如果以字母开头，则为函数
					$type = Operator::FUNCTIONS;
				continue;
			}else if($char == '$'){
				$element[] = $char;
				if($type == null)//如果以$符开头，则为变量
					$type = Operator::VARIABLE;
				continue;
			}else if($char == ',' || $char=='{' || $char == '}' || $char == ':' || $char=='_'){//这些字符不是开头，但可以在中间使用
				$element[] = $char;
				continue;
			}
			
			switch ($char){//如果是运算符号
				case '+':
				case '-':
				case '*':
				case '/':
					if($type== Operator::FUNCTIONS){//如果在一个函数中，压入elements中，继续读取运算式
						$element[]= $char;
						break;
					}
					
					//如果element不为空，将element清空，入栈
					if( !empty($element) ){
						$variable = implode('', $element);
						$stack2[] = new Operator($type, $variable);
						$element = array();
						$type = null;
					}
					
					//如果当前运算符优先级高于栈顶运算符，将当前运算符入栈。
					//否则，将运算符栈中的元素依次弹出，直到碰到栈顶运算符优先级低于当前运算符
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
				case '('://如果是左括号，并且不是在函数中，则把(入到stack1栈
					if($type==Operator::FUNCTIONS){
						$element[]= $char;
						$func_brackets ++;
					}else{
						$stack1[] = $char;
						
						if( !empty($element) ){//如果elemnt不为空，入到stack2中
							$variable = implode('', $element);
							$stack2[] = new Operator($type, $variable);
							$element = array();
							$type = null;
						}
						
					}
					break;
					
				case ')':
					if($type==Operator::FUNCTIONS){//如果当前type为function
						
						$func_brackets --;
						if($func_brackets == 0){//如果左右括号个数正好是偶数
							$element[]= $char;
							
							$variable = implode('', $element);
							$stack2[] = new Operator($type, $variable);
							
							$element = array();
							$type = null;
						}else{//不是偶数，说明函数还未读完
							$element[]= $char;
						}
						
						
					}else{//非function，把当前element入栈并清空
						
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
		
		if( !empty($element) ){//将剩余的element入栈
				$stack2[] = new Operator($type, implode('', $element));
		}
		
		while(count($stack1) > 1){//将运算符栈中的元素依次送到stack1中
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
	
	/**
	 * 计算表达式
	 * @param unknown $rule_data
	 * @param unknown $key
	 * @throws Exception
	 * @return mixed|boolean
	 */
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
					$value = $operator->getValue($rule_data,$key);
					/* if ($operator->type == Operator::FUNCTIONS ){
						echo $operator."::$value\n";
					}  */
					array_push($stack2,  $value);
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
