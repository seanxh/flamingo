<?php

/**
 * This is the model class for table "budget_unit".
 *
 * The followings are the available columns in table 'budget_unit':
 * @property integer $id
 * @property string $log_name
 * @property string $table_name
 * @property int $database_id
 * @property string time_column
 * @property int log_cycle
 * @property int log_type 
 */
class log_config extends CActiveRecord
{
	const  NOCYCLE = 1;
	const WITHCYCLE = 0;
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
		return 'log_config';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('log_name,table_name,database_id,time_column,log_cycle,log_type', 'required'),
			array('log_name,table_name,database_id,time_column,log_cycle,log_type', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,log_name,table_name,database_id,time_column,log_cycle,log_type', 'safe', 'on'=>'search'),
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
			'database'	=> array(self::BELONGS_TO, 'database_config', 'database_id'),
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
			'log_type'=>'日志类型',
		);
	}

}// end class
