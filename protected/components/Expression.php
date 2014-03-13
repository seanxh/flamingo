<?php

/**
 * 表达式 $ip+prev($ip) > 300
 * @author seanxh
 */
class Expression {
	
	private $_left_expression = null;
	
	private $_right_expression = null;
	
	public $logic = null;
	
	private $_compare = null;
	
	public $result = null;
	
	const LOGICAND = 'and';
	
	const  LOGICOR = 'or';
	
	/**
	 * 构造方法
	 * @param unknown $left_expression
	 * @param unknown $right_expression
	 * @param unknown $compare
	 * @param unknown $logic
	 */
	function __construct($left_expression,$right_expression,$compare,$logic){
		//子表达式
		$this->_left_expression = new ChildExpression($left_expression);
		$this->_right_expression = new ChildExpression($right_expression);
		
		$this->_compare = $compare;
		$this->logic = $logic;
		
	}
	
	function preload($rule_data){
		$this->_left_expression->preloadData($rule_data);
	}
	
	
	function bool($rule_data,$key){
// 		echo $this->_left_expression."\n";
		$left_value = $this->_left_expression->calc($rule_data,$key);
// 		echo $left_value."\n";
// 		echo $this->_right_expression."\n";
		$right_value = $this->_right_expression->calc($rule_data,$key);
// 		echo $right_value."\n";
		switch ($this->_compare){
			case '=':
				$this->result = $left_value==$right_value;
				break;
			case '!=':
				$this->result = $left_value != $right_value;
				break;
			case '>': 
				$this->result = $left_value > $right_value;
				break;
			case '>=':
				$this->result = $left_value >= $right_value;
				break;
			case '<':
				$this->result = $left_value < $right_value;
				break;
			case '<=':
				$this->result = $left_value <= $right_value;
				break;
		}
		
		return $this->result ;
		
	}	
	
	
}