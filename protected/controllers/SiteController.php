<?php
class SiteController extends CController{
	
	public function init(){}
	
	
	public function actionIndex(){
		
// 		$time = time();
		$time = strtotime('2014-03-09 23:19:00');
		$monitor_id = 1;
		$rule = monitor_rule::model()->findByPk($monitor_id);
		
		$log_config = $rule->log_config;
		
		$cycle_time = intval($time/$log_config->log_cycle)*$log_config->log_cycle;
		
		$database = $log_config->database;
		
		//等待本周期的数据入库
		//  		sleep($rule->wait_time);
		
		$log_dsn = $database->type.':host='.$database->host.';port='.$database->port.';dbname='.$database->dbname;
		$rule_data = new RuleData($log_dsn,$database->user,$database->passwd, 'utf8',$log_config,$rule,$cycle_time);
			
		//监控策略条件表达式(一个监控策略有可能有多个表达式，多个表达式可能是逻辑“或”，“与”的关系
		$condition = $rule->condition;
		
		$expressions = array();
		
		foreach($condition as $con){
			$expression = array();
			//表达式逻辑
			$expression['logic'] = $con->logic_operator;
			//表达式比较运算符
			$expression['compare'] = $con->comparison_operator;
			//依次取出左式和右式
			foreach($con->operation_expression as $child_expression){
				$expression[$child_expression->left_or_right] = $child_expression->expression;
			}
			//右式必须是一个数字
			if( !is_numeric($expression['right']) ) throw new Exception('右式必须是一个表达式');
			
			$expressions[]  = new Expression($expression['left'], $expression['right'], $expression['compare'] , $expression['logic'], $rule_data);
		}
		
		
		$condition = new Condition($expressions,$rule_data);
		$condition->preload();
		
		var_dump($rule_data[0]);
		var_dump($rule_data[1]);
		
	}
	
}