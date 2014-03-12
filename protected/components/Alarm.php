<?php
class Alarm{
	
	/**
	 * @var monitor_rule
	 */
	public $rule;
	
	public function __construct($rule){
		$this->rule = $rule;
	}
	
	
	public function oneMail($alert_data){
		$title = $this->getData($this->rule->alert_title,$alert_data,key($alert_data[0]));
		$contents = array();
		foreach($alert_data[0] as $key=>$value){
			$contents[] = $this->getData($this->rule->alert_content,$alert_data,$key);
		}
		echo $title;
		echo "<table border=1>";
		echo $this->rule->alert_head;
		echo implode('',$contents);
		echo "</table>";
	}
	
	public function getData($alert_title,$alert_data,$key){
// 		echo $alert_title."</br>";
// 		echo $key."</br>";
		preg_match_all('/\[([^\[\]]+)\]/',$alert_title,$title_expressions);
		
		if( !empty($title_expressions)){
			
			$values = array();
			foreach ($title_expressions[1] as  $expression){
				$child_expression = new ChildExpression($expression, $alert_data);
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