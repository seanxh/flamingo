<?php

class  FunctionsStack{
	
	const OPERATOR = 'operator';
	const FUNCTIONS = 'function';
	const INTEGER = 'integer';
	const VARIABLE = 'variable';
	const BRACKET = 'bracket'; 
	const PENDING = 'pending';
	const STRING = 'string';
	const ARRAYS = 'array';
	
	
	private $_stack=array();
	
	public function __construct(){
		
	}
	
	public function get(){
		return $this->_stack;
	}
	
	function push($type,$value){
		if($type == self::FUNCTIONS && $value == self::ARRAYS){
			$type = self::ARRAYS;
		}
		$this->_stack[] = array($type,$value);
	}
	
	function pop(){
		return array_pop($this->_stack);
	}
	
	
}