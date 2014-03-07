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
class database_config extends CActiveRecord
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
		return 'database_config';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type,dbname,host,port,user,passwd', 'required'),
			array('type,dbname,host,port,user,passwd', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id,type,dbname,host,port,user,passwd', 'safe', 'on'=>'search'),
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
			'log_config'	=> array(self::HAS_MANY, 'log_config', 'database_id'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type'=>'数据库类型',
			'dbname'=>'数据库名称',
			'host'=>'数据库地址',
			'port'=>'数据库端口',
			'user'=>'数据库用户名',
			'passwd'=>'数据库密码',
		);
	}

}// end class
