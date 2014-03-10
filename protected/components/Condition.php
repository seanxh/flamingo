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
	
		$alert_data = array();
		
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
			
			if ( $boolean )
				$alert_data[0][$key] = $this->rule_data[0][$key];
			
		}
		
		var_dump($alert_data);
		
	}
	
	
}