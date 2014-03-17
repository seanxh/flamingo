<?php

/**
 * 表达式 $ip+prev($ip) > 300
 * @author seanxh
 */
class Expression {
	/**
	 * 左表达式
	 * @var ChildExpression
	 */
	private $_left_expression = null;
	
	/**
	 * 右表达式
	 * @var ChildExpression
	 */
	private $_right_expression = null;
	
	/**
	 * 逻辑运算符
	 * @var unknown
	 */
	public $logic = null;
	
	private $_compare = null;
	
	public $result = null;
	
	const LOGICAND = 'and';
	
	const  LOGICOR = 'or';
	
	/**
	 * 构造方法
	 * @param string $left_expression 左表达式
	 * @param string $right_expression 右表达式
	 * @param string $compare 比较运算符
	 * @param string $logic 逻辑运算符
	 */
	function __construct($left_expression,$right_expression,$compare,$logic){
		//子表达式
		$this->_left_expression = new ChildExpression($left_expression);
		
// 		echo $this->_left_expression;
		$this->_right_expression = new ChildExpression($right_expression);
		
		$this->_compare = $compare;
		$this->logic = $logic;
		
	}
	
	/**
	 * 提前加载并Group数据
	 * @param RuleData $rule_data
	 */
	function preload($rule_data){
		$this->_left_expression->preloadData($rule_data);
	}

	/**
	 * 依据数据源，计算此表达式的布尔值 True/False
	 * @param RuleData $rule_data
	 * @param string/int $key
	 * @return boolean
	 */
	function bool($rule_data,$key){
		$left_value = $this->_left_expression->calc($rule_data,$key);
		$right_value = $this->_right_expression->calc($rule_data,$key);
		switch ($this->_compare){
			case '=':
			case '==':
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
			case 'in':
				$this->result = in_array($left_value, $right_value);
				break;
		}
		
		return $this->result ;
		
	}	
	
}