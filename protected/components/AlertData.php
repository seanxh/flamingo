<?php
class AlertData extends RuleData{
	
	/**
	 * @param RuleData $rule_data
	 */
	public function __construct($rule_data){
		parent::__construct( $rule_data->dsn,  $rule_data->username, $rule_data->password, $rule_data->charset, $rule_data->log_config, $rule_data->rule,$rule_data->current_cycle_timestamp);
	}
	
	
	public function  preloadGroup(){
	}
	
	public function offsetGet ($offset) {
		if(!isset( $this->_data[$offset])){
			return null;
		}
		return $this->_data[$offset];
	}
	
}