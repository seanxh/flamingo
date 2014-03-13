<?php
class  AlertDeployRule{
	
	function __construct(){
		
	}
	
	
	function check($alert_deploy_rule){
		if( strpos($alert_deploy_rule, '|') !== false) {
			if( strpos($alert_deploy_rule, '&') !== false ) return false;
			return $this->check_or($alert_deploy_rule);
		}
		if( strpos($alert_deploy_rule, '&') !== false){
			if( strpos($alert_deploy_rule, '|') !== false ) return false;
			return $this->check_and($alert_deploy_rule);
		}
		return $this->check_or($alert_deploy_rule);
	}
	
	function check_and($alert_deploy_rule){
		$rules = explode('&', trim($alert_deploy_rule));
		foreach ($rules as $rule){
			$child_rules = explode('/',trim($rule));
			$method_name = trim($child_rules[0]);
			$parameters =  trim($child_rules[1]);
			if( count($child_rules) != 2) return false;
		
			if( !method_exists($this, $method_name))  return false;
		
			if( !$this->$method_name($parameters) ) return false;
		}
		return true;
	}
	
	function check_or($alert_deploy_rule){
		$rules = explode('|', trim($alert_deploy_rule));
		foreach ($rules as $rule){
			$child_rules = explode('/',trim($rule));
			$method_name = trim($child_rules[0]);
			$parameters =  trim($child_rules[1]);
			if( count($child_rules) != 2) continue;
				
			if( !method_exists($this, $method_name))  continue;
			if( $this->$method_name($parameters) ) return true;
		}
		return false;
	}
	
	function week($check){
		$week_day = date('w');
		if($week_day == 0) $week_day = 7;
		$week_days = explode(',', $check);
		
		if ( !in_array($week_day, $week_days))	return FALSE;
		
		return true;
	}
	
	function day($check){
		$day = date('j');
	
		$days = explode(',', $check);
	
		if ( !in_array($day, $days))	return FALSE;
	
		return true;
	}
	
	function month($check){
		$month = date('n');
		
		$monthes = explode(',', $check);
		
		if ( !in_array($month, $monthes))	return FALSE;
		
		return true;
	}
	
	function hour($check){
		$hour = date('G');
		$hours = explode(',', $check);
		if ( !in_array($hour, $hours))	return FALSE;
	
		return true;
	}
	
	
}