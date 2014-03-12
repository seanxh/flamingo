<?php

/**
 * This is the model class for table "budget_unit".
 *
 * The followings are the available columns in table 'budget_unit':
 * @property integer $id
 * @property string $name
 * @property string $op_users
 * @property string $log
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
			array('log_id,monitor_name,filter_data_condition,is_alert_everytime,is_alert_everytime,alert_in_cycles,alert_when_gt_times,alert_title,alert_head,alert_content,alert_receiver,wait_time,status,fields,conditions', 'required'),
			array('log_id,monitor_name,filter_data_condition,is_alert_everytime,is_alert_everytime,alert_in_cycles,alert_when_gt_times,alert_title,alert_head,alert_content,alert_receiver,wait_time,status,fields,conditions', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,log_id,monitor_name,filter_data_condition,is_alert_everytime,is_alert_everytime,alert_in_cycles,alert_when_gt_times,alert_title,alert_head,alert_content,alert_receiver,wait_time,status,fields,conditions', 'safe', 'on'=>'search'),
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
		);
	}

}// end class
