<?php

/**
 * 表达式 $ip+prev($ip) > 300
 * @author seanxh
 */
class Expression {
	
	private $_left_expression = null;
	
	private $_right_expression = null;
	
	private $_logic = null;
	
	private $_compare = null;
	
	/**
	 * 构造方法
	 * @param unknown $left_expression
	 * @param unknown $right_expression
	 * @param unknown $compare
	 * @param unknown $logic
	 */
	function __construct($left_expression,$right_expression,$compare,$logic,$rule_data){
		//子表达式
		$this->_left_expression = new ChildExpression($left_expression,$rule_data);
		$this->_right_expression = new ChildExpression($right_expression,$rule_data);
		
		$this->_compare = $compare;
		$this->_logic = $logic;
		
	}
	
	function preload($rule_data){
		$this->_left_expression->preloadData($rule_data);
	}
	
	
	function bool($rule_data,$key){
		
		$left_value = $this->_left_expression->calc($rule_data,$key);
		
		$right_value = $this->_right_expression->calc($rule_data,$key);
		switch ($this->_compare){
			case '=':
				return $left_value==$right_value;
				break;
			case '!=':
				return $left_value != $right_value;
				break;
			case '>': 
				return $left_value > $right_value;
				break;
			case '>=':
				return $left_value >= $right_value;
				break;
			case '<':
				return $left_value < $right_value;
				break;
			case '<=':
				return $left_value <= $right_value;
				break;
		}
		
	}	
	
	
}