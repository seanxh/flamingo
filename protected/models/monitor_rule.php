<?php

/**
 * This is the model class for table "budget_unit".
 *
 * The followings are the available columns in table 'budget_unit':
 * @property integer $id
 * @property string $log_id
 * @property string $monitor_name
 * @property string $filter_fields
 * @property string $filter_conditions
 * @property int $is_alert_everytime
 * @property int $alert_in_cycles
 * @property int $alert_when_gt_times
 * @property string $alert_title
 * @property string $alert_head
 * @property string $alert_content
 * @property int $alert_deploy_id
 * @property int $wait_time
 * @property int $status
 * 
 * @property log_config $log_config
 * @property monitor_condition $condition
 * @property alert_deploy $alert_deploy
 * @property monitor_rule_join $rule_join
 */
class monitor_rule extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return budget_unit the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'monitor_rule';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('log_id,monitor_name,alert_deploy_id,filter_fields,filter_data_condition,is_alert_everytime,is_alert_everytime,alert_in_cycles,alert_when_gt_times,alert_title,alert_head,alert_content,alert_receiver,wait_time,status,fields,conditions', 'required'),
			array('log_id,monitor_name,alert_deploy_id,filter_fields,filter_data_condition,is_alert_everytime,is_alert_everytime,alert_in_cycles,alert_when_gt_times,alert_title,alert_head,alert_content,alert_receiver,wait_time,status,fields,conditions', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,log_id,monitor_name,alert_deploy_id,filter_fields,filter_data_condition,is_alert_everytime,is_alert_everytime,alert_in_cycles,alert_when_gt_times,alert_title,alert_head,alert_content,alert_receiver,wait_time,status,fields,conditions', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'log_config'	=> array(self::BELONGS_TO, 'log_config', 'log_id'),
			'condition' => array(self::HAS_MANY,'monitor_condition','rule_id'),
			'alert_deploy'=>array(self::BELONGS_TO,'alert_deploy','alert_deploy_id'),
			'rule_join'=>array(self::HAS_MANY,'monitor_rule_join','rule_id'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'log_name' => '日志名称',
			'table_name' => '日志表名称',
			'database_id' => '数据库',
			'time_column'=>'时间字段',
			'log_cycle'=>'日志周期(秒)',
			'log_id' => '日志ID',
			'monitor_name'=>'监控名称',
			'filter_fields'=>'指定字段',
			'filter_conditions'=>'指定条件',
			'is_alert_everytime'=>'是否每次',
			'alert_in_cycles'=>'在N个周期内监控',
			'alert_when_gt_times'=>'当异常大于N次时报警',
			'alert_title'=>'报警标题',
			'alert_head'=>'报警内容表头',
			'alert_content'=>'报警内容',
			'alert_deploy_id'=>'报警接收人配置',
			'wait_time'=>'等待时间',
			'status'=>'监控状态',				
		);
	}

}// end class
