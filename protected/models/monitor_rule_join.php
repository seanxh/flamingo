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
class monitor_rule_join extends CActiveRecord
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
		return 'monitor_rule_join';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('rule_id,table_name,left_condition,right_condition', 'required'),
			array('rule_id,table_name,left_condition,right_condition', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,rule_id,table_name,left_condition,right_condition', 'safe', 'on'=>'search'),
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
			'rule' => array(self::BELONGS_TO,'monitor_rule','rule_id'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'rule_id' => '日志名称',
			'table_name' => '日志表名称',
			'left_condition' => '左表达式',
			'right_condition'=>'右表达式'
		);
	}

}// end class
