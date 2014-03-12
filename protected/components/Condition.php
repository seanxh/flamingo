<?php
class Condition {
	
	/**
	 * @var Array[Expression]
	 */
	public $expressions;
	/**
	 * @var RuleData
	 */
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
	
		$alert_data = new AlertData($this->rule_data->current_cycle_timestamp);
		$alert_arr = array();
		 
		foreach( $this->rule_data[0] as $key=>$value){
			
			foreach ($this->expressions as $expression){
				$expression->bool($this->rule_data,$key) ;
			}
			
			if( $this->expressions[0]->logic == Expression::LOGICAND ){
				$boolean = true;
			}else{
				$boolean = false;
			}
			
			foreach ($this->expressions as $expression){
				if( strtolower($expression->logic) == Expression::LOGICAND  ) {
					
					if( !$expression->result ){
						$boolean = false;
						break;
					}
					
				}else if( strtolower($expression->logic) == Expression::LOGICOR  ){
					
					if( $expression->result ){
						$boolean = true;
						break;
					}
					
				}else{
					$boolean = false;
				}
			}
			
			if ($boolean ){
				foreach ($this->rule_data as $cycle=>$value){
					if ( !isset($alert_arr[$cycle]) ) {
						$alert_arr[$cycle] = array();
					}
					$alert_arr[$cycle][$key] = $value[$key];
				}
			}
			
		}
		
		foreach ( $alert_arr as $cycle=>$value){
			$alert_data[$cycle] = $value;
		}
		
// 		$alert_data->pp();
		
		return $alert_data;
		
	}
	
	
}