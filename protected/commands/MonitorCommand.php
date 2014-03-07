<?php
class MonitorCommand extends CConsoleCommand{
	
	/**
	 * 初始化
	 * @see CConsoleCommand::init()
	 */
	public function init(){
		parent::init();
	}
	
	/**
	 * @param int $monitor_id 监控策略ID
	 * 报警入口 
	 */
	public function actionIndex($monitor_id) {
		$time = time();
		
		$rule = monitor_rule::model()->findByPk($monitor_id);
		
		$log_config = $rule->log_config;
		
		$cycle_time = intval($time/$log_config->log_cycle)*$log_config->log_cycle;
		
		$database = $log_config->database;
		
		//等待本周期的数据入库
//  		sleep($rule->wait_time);
		
		$log_dsn = $database->type.':host='.$database->host.';port='.$database->port.';dbname='.$database->dbname;
		$rule_data = new RuleData($log_dsn,$database->user,$database->passwd, 'utf8',$log_config,$rule,$cycle_time);
 		
		$condition = $rule->condition;
		
		$expressions = array();
		
		foreach($condition as $con){
			$expression = array();
			$expression['logic'] = $con->logic_operator;
			$expression['compare'] = $con->comparison_operator;
			foreach($con->operation_expression as $child_expression){
				$expression[$child_expression->left_or_right] = $child_expression->expression;
			}
			$expression = new Expression($expression['left'], $expression['right'], $expression['compare'] , $expression['logic'], $rule_data);
// 			$expressions[] = $expression;
		}
		
		
	}
	
} 