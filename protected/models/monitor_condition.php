<?php

/**
 * This is the model class for table "budget_unit".
 *
 * The followings are the available columns in table 'budget_unit':
 * @property integer $id
 * @property int $rule_id
 * @property string $logic_operator
 * @property string $comparison_operator
 */
class monitor_condition extends CActiveRecord
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
		return 'monitor_condition';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('rule_id,logic_operator,comparison_operator', 'required'),
			array('logic_operator,comparison_operator', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,rule_id,logic_operator,comparison_operator', 'safe', 'on'=>'search'),
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
			'rule'	=> array(self::BELONGS_TO, 'monitor_rule', 'rule_id'),
			'operation_expression' => array(self::HAS_MANY, 'monitor_operation_expression', 'condition_id'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'rule_id' => '报警策略ID',
			'logic_operator' => '逻辑运算符',
			'comparison_operator' => '比较运算符',
		);
	}

}// end class
