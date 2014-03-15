<?php
class RuleData extends  CDbConnection implements ArrayAccess,Iterator,Countable{
	
	public $current_cycle_timestamp;
	
	public $schema ;
	
	public $log_config;
	
	public $rule;
	
	public $_log_type ;
	
	private $_log_cycle;
	
	private $_log_time_column;
	
	private $_log_time_column_type;
	
	private $_condition;
	
	private $_table;
	
	private $_filed;
	
	protected  $_data; 
	
	private $_join;
	
	/**
	 * 
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param string $charset
	 * @param log_config $log_config
	 * @param monitor_rule $rule
	 * @param int $cycle_timestamp
	 */
	public function __construct($dsn,$username,$password,$charset,$log_config,$rule,$cycle_timestamp=0){
		parent::__construct($dsn,$username,$password);
		$this->charset=$charset;
		$this->active=true;
		$this->current_cycle_timestamp = $cycle_timestamp;
		
		$this->setLog($log_config);
		$this->setRule($rule);
	}
	
	/**
	 * @param log_config $log_config
	 */
	public function setLog($log_config){
		$this->schema =  $this->getSchema()->getTable($log_config->table_name);
		
		$this->_table = $log_config->table_name;
		
		$this->_log_time_column = $log_config->time_column;
		
		$this->_log_time_column_type = $this->schema->columns[$log_config->time_column]->type;
		
		$this->_log_cycle = $log_config->log_cycle;
		
		$this->_log_type = $log_config->log_type;
		
		if( !$this->current_cycle_timestamp )
			$this->current_cycle_timestamp = intval( time() / $this->_log_cycle )  * $this->_log_cycle;
	}
	
	public function pp(){
		var_dump($this->_data);
	}
	
	/**
	 * @param monitor_rule $rule
	 */
	public function setRule($rule){
		$this->rule = $rule;
		$this->_filed = empty($rule->filter_fields) ? '*' :  $rule->filter_fields;
		$this->_condition= empty($rule->filter_conditions) ? '' :  $rule->filter_conditions;
		$this->_join  = empty($rule->rule_join) ? array() : $rule->rule_join;
	}
	
	/**
	 * 预加载函数
	 * @param unknown $group
	 * @param number $cycle
	 */
	public function preloadGroup($group,$cycle=1){
		$first = $this->offsetGet(0);
		
		$first_group = array();
		foreach ( $first as $key=>$value){
			$new_key = '';
			foreach ($group as $g){
				$new_key .= $value[$g].'|';	
			}
			$first_group[$new_key] = $value;
		}
		
		$this->offsetSet(0, $first_group);
		
		$values = $this->offsetGet($cycle);
		
		$new_values = array();
		foreach ($values as $key=>$value){
			$new_key = '';
			foreach ($group as $g){
				$new_key .= $value[$g].'|';
			}
			$new_values[$new_key] = $value;
		}
		
		$this->offsetSet($cycle, $new_values);
		
	}
	
	public function parseCondition($condition){
		preg_match_all('/\[([^\[\]]+)\]/',$condition,$expressions);
	
		if( !empty($expressions)){
			
			$values = array();
			foreach ($expressions[1] as  $expression){
				$child_expression = new ChildExpression($expression);
				$values[]  =  array(
					$expression,
					$child_expression->calc(array(),''),
				);
			}
			foreach ($values as $value){
				$condition = str_replace("[{$value[0]}]", $value[1], $condition);
			}
			
		}
	
		return $condition;
	}
	
	private function _get($index){
		
		$cycle_where = '';
		
		if ( $this->_log_type == log_config::WITHCYCLE )
			$cycle_where = $this->_log_time_column.'>='.$this->calcCycle($index).' and '.$this->_log_time_column.'<'.$this->calcCycle($index-1);
		
		$user_condition = $this->parseCondition($this->_condition);
		
		$condition = ''; 
		
		if( !empty($user_condition) ){
			$condition = $user_condition;
		}
		
		if( !empty($cycle_where) )
			$condition = $condition.' and '.$cycle_where;
		
		if( empty($condition) ) throw Exception('the rule '.$this->rule->id.' was monitor as a empty condition.Please check');
		
 		$command=$this->createCommand();
 		$reader = $command->select($this->_filed)->from($this->_table);
 		
 		foreach ($this->_join as $join){
 			$reader = $reader->join($join->table_name,$join->left_condition.'='.$join->right_condition);
 		}
 		
 		$reader = $reader->where($condition)
 		->queryAll();
 		
		return $reader;
	}
	
	/**
	 * 判断该时间点属于哪个周期。
	 * 本周期为 0
	 * 前一个周期为 1
	 * @param unknown $type
	 * @return number
	 */
	public function judgeCycle($time){
	
		if ( $this->_log_time_column_type  == 'integer')
			$interval =  $this->current_cycle_timestamp  - intval( $time ) ;
		else
			$interval = $this->current_cycle_timestamp  - strtotime($time);
	
		if( $interval < 0 )
			return 0;
		return ceil( $interval /   $this->_log_cycle );
	}
	
	/**
	 * 根据周期索引，返回周期的起始时间
	 * @param unknown $index
	 * @return number|string
	 */
	public function calcCycle($index){
		if ( $this->_log_time_column_type  == 'integer')
			return $this->current_cycle_timestamp - $index*$this->_log_cycle;
		else
			return date( "'Y-m-d H:i:s'",$this->current_cycle_timestamp - $index*$this->_log_cycle );
	}
	
	public function calcCycleIndex($str_time){
		$time = strtotime($str_time);
		return ceil( (  $this->current_cycle_timestamp - $time ) /  $this->_log_cycle ) ;
	}
	
	public function count() {
		return count($this->_data);
	}
 	
 	function rewind() {
        reset($this->_data);
    }

    function current() {
        return current($this->_data);
    }

    function key() {
        return key($this->_data);
    }

    function next() {
        next($this->_data);
    }

    function valid() {
         return ( $this->current() !== false ); 
    }
	
	/**
	 * @param offset
	 */
	public function offsetExists ($offset) {
		return isset($this->_data[$offset] );
	}
	
	/**
	 * @param offset
	 */
	 public function offsetGet ($offset) {
	 	if(!isset( $this->_data[$offset])){
	 		$this->_data[$offset] = $this->_get($offset);
	 	}
	 	return $this->_data[$offset];
	 }
	
	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet ($offset, $value) {
			$this->_data[$offset] = $value;
	}
	
	/**
	 * @param offset
	 */
	public function offsetUnset ($offset) {
		if(isset($this->_data[$offset]))
			unset($this->_data[$offset]);
	}
	
}