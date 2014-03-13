<?php

/**
 * This is the model class for table "budget_unit".
 *
 * The followings are the available columns in table 'budget_unit':
 * @property integer $id
 * @property string $alert_deploy_id
 * @property string $receiver
 * @property string $rule
 * @property string $type
 * 
 */
class alert_receiver extends CActiveRecord
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
		return 'alert_receiver';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id,alert_deploy_id,receiver,rule,type', 'required'),
			array('receiver,rule,type', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,alert_deploy_id,receiver,rule,type', 'safe', 'on'=>'search'),
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
			'alert_deploy'	=> array(self::BELONGS_TO, 'alert_deploy', 'alert_deploy_id'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'alert_deploy_id'=>'报警策略ID',
			'receiver'=>'接收人',
			'rule'=>'是否接收报警判定式',
			'type'=>'接收报警类型',
		);
	}

}// end class
