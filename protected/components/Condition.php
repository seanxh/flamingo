<?php
class Condition {
	
	/**
	 * @var Array[Expression]
	 */
	public $expressions;
	
	public $rule_data;
	
	/**
	 * 报警条件判断
	 * @param 表达式 $expressions
	 * @param 日志数据 $rule_data
	 */
	function __construct($expressions,$rule_data){
		//子表达式数组
		$this->expressions = $expressions;
		
		$this->rule_data = $rule_data;
	}
	
	public function preload(){
		
		foreach ($this->expressions as $expression){
			$expression->preload($this->rule_data);
		}
		
	}
	
	public function judgeCondition(){
		
		foreach( $this->rule_data[0] as $key=>$value){
			
			foreach ($this->expressions as $expression){
				var_dump( $expression->bool($this->rule_data,$key) );
			}
		}
		
	}
	
	
}