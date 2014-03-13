<?php
class Alarm{
	
	/**
	 * @var monitor_rule
	 */
	public $rule;
	
	/**
	 * @var AlertDeployRule
	 */
	public $alert_deploy_rule;
	
	public function __construct($rule){
		$this->rule = $rule;
		$this->alert_deploy_rule = new AlertDeployRule();
	}
	
	public function getAlertReceiver(){
		$receivers_arr = array();
		$alert_deploy = $this->rule->alert_deploy;
		$receivers = $alert_deploy->receiver;
		if( $receivers != NULL){
			foreach ($receivers as $receiver){
				if( !empty($receiver->rule)){
						if( ! $this->alert_deploy_rule->check($receiver->rule) )
							continue;
				}
				if ( !is_array($receivers_arr[$receiver->type])) $receivers_arr[$receiver->type] = array();
				array_push($receivers_arr[$receiver->type],$receiver->receiver);
			}
		}
		return $receivers_arr;
	}
	
	public function oneMail($alert_data){
		$receiver = $this->getAlertReceiver();
		
		$title = $this->getData($this->rule->alert_title,$alert_data,key($alert_data[0]));
		$contents = array();
		foreach($alert_data[0] as $key=>$value){
			$contents[] = $this->getData($this->rule->alert_content,$alert_data,$key);
		}
		var_dump( $receiver );
		echo $title;
		echo "<table border=1>";
		echo $this->rule->alert_head;
		echo implode('',$contents);
		echo "</table>";
	}
	
	public function multiMail($alert_data){
		$receiver = $this->getAlertReceiver();
		foreach($alert_data[0] as $key=>$value){
			$title = $this->getData($this->rule->alert_title,$alert_data,$key);
			$content= $this->getData($this->rule->alert_content,$alert_data,$key);
			var_dump( $receiver );
			echo $title;
			echo "<table border=1>";
			echo $this->rule->alert_head;
			echo $content;
			echo "</table>";
		}
	}
	
	public function mergeMail($alert_data){
		
	}
	
	public function getData($alert_title,$alert_data,$key){
// 		echo $alert_title."</br>";
// 		echo $key."</br>";
		preg_match_all('/\[([^\[\]]+)\]/',$alert_title,$title_expressions);
		
		if( !empty($title_expressions)){
			
			$values = array();
			foreach ($title_expressions[1] as  $expression){
				$child_expression = new ChildExpression($expression);
// 				echo $child_expression;
// 				$temp = $alert_data[0];
				$values[]  =  array(
										$expression,
										$child_expression->calc($alert_data,$key ),
									);
			}
			foreach ($values as $value){
				$alert_title = str_replace("[{$value[0]}]", $value[1], $alert_title);
			}
			
		}
		
		return $alert_title;
	}
	
}