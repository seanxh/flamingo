<?php
class AlertData extends RuleData{
	
	public function __construct($cycle_timestamp=0){
		$this->current_cycle_timestamp = $cycle_timestamp;
	
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