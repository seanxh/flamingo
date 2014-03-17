<?php

/**
 * This is the model class for table "budget_unit".
 *
 * The followings are the available columns in table 'budget_unit':
 * @property integer $id
 * @property string $condition_id
 * @property string $left_or_right
 * @property string $expression
 */
class monitor_operation_expression extends CActiveRecord
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
		return 'monitor_operation_expression';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('condition_id,left_or_right,expression', 'required'),
			array('condition_id,left_or_right,expression', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,condition_id,left_or_right,expression', 'safe', 'on'=>'search'),
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
			'condition'	=> array(self::BELONGS_TO, 'monitor_condition', 'condition_id'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'condition_id'=>'报警判断条件ID',
			'left_or_right' => '是左式还是右式',
			'expression' => '表达式',
		);
	}

}// end class
