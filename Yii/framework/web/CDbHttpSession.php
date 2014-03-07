<?php
/**
 * CDbHttpSession class
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbHttpSession extends {@link CHttpSession} by using database as session data storage.
 *
 * CDbHttpSession stores session data in a DB table named 'YiiSession'. The table name
 * can be changed by setting {@link sessionTableName}. If the table does not exist,
 * it will be automatically created if {@link autoCreateSessionTable} is set true.
 *
 * The following is the table structure:
 *
 * <pre>
 * CREATE TABLE YiiSession
 * (
 *     id CHAR(32) PRIMARY KEY,
 *     expire INTEGER,
 *     data TEXT
 * )
 * </pre>
 *
 * CDbHttpSession relies on {@link http://www.php.net/manual/en/ref.pdo.php PDO} to access database.
 *
 * By default, it will use an SQLite3 database named 'session-YiiVersion.db' under the application runtime directory.
 * You can also specify {@link connectionID} so that it makes use of a DB application component to access database.
 *
 * When using CDbHttpSession in a production server, we recommend you pre-create the session DB table
 * and set {@link autoCreateSessionTable} to be false. This will greatly improve the performance.
 * You may also create a DB index for the 'expire' column in the session table to further improve the performance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbHttpSession.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.web
 * @since 1.0
 */
class CDbHttpSession extends CHttpSession
{
	/**
	 * @var string the ID of a {@link CDbConnection} application component. If not set, a SQLite database
	 * will be automatically created and used. The SQLite database file is
	 * is <code>protected/runtime/session-YiiVersion.db</code>.
	 */
	public $connectionID;
	/**
	 * @var string the name of the DB table to store session content.
	 * Note, if {@link autoCreateSessionTable} is false and you want to create the DB table manually by yourself,
	 * you need to make sure the DB table is of the following structure:
	 * <pre>
	 * (id CHAR(32) PRIMARY KEY, expire INTEGER, data TEXT)
	 * </pre>
	 * @see autoCreateSessionTable
	 */
	public $sessionTableName='YiiSession';
	/**
	 * @var boolean whether the session DB table should be automatically created if not exists. Defaults to true.
	 * @see sessionTableName
	 */
	public $autoCreateSessionTable=true;
	/**
	 * @var CDbConnection the DB connection instance
	 */
	private $_db;

	private $_orig_data;


	/**
	 * Returns a value indicating whether to use custom session storage.
	 * This method overrides the parent implementation and always returns true.
	 * @return boolean whether to use custom storage.
	 */
	public function getUseCustomStorage()
	{
		return true;
	}

	/**
	 * Creates the session DB table.
	 * @param CDbConnection $db the database connection
	 * @param string $tableName the name of the table to be created
	 */
	protected function createSessionTable($db,$tableName)
	{
		$sql="
CREATE TABLE $tableName
(
	id CHAR(32) PRIMARY KEY,
	expire INTEGER,
	data TEXT
)";
		$db->createCommand($sql)->execute();
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CException if {@link connectionID} does not point to a valid application component.
	 */
	protected function getDbConnection()
	{
		if($this->_db!==null)
			return $this->_db;
		else if(($id=$this->connectionID)!==null)
		{
			if(($this->_db=Yii::app()->getComponent($id)) instanceof CDbConnection)
				return $this->_db;
			else
				throw new CException(Yii::t('yii','CDbHttpSession.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$id)));
		}
		else
		{
			$dbFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'session-'.Yii::getVersion().'.db';
			return $this->_db=new CDbConnection('sqlite:'.$dbFile);
		}
	}

	/**
	 * Session open handler.
	 * Do not call this method directly.
	 * @param string $savePath session save path
	 * @param string $sessionName session name
	 * @return boolean whether session is opened successfully
	 */
	public function openSession($savePath,$sessionName)
	{
		$db=$this->getDbConnection();
		$db->setActive(true);

		if($this->autoCreateSessionTable)
		{
			$sql="DELETE FROM {$this->sessionTableName} WHERE expire<".time();
			try
			{
				$db->createCommand($sql)->execute();
			}
			catch(Exception $e)
			{
				$this->createSessionTable($db,$this->sessionTableName);
			}
		}
		return true;
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{

		$db=$this->getDbConnection();
		$trans = $db->beginTransaction();
		$now=time();
		$sql="
SELECT data FROM {$this->sessionTableName}
WHERE expire>$now AND id=:id
";
		$this->_orig_data = $data=$db->createCommand($sql)->bindValue(':id',$id)->queryScalar();
		$trans->commit();
		return $data===false?'':$data;
	}

	/**
	 * Session write handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id,$data)
	{
		// exception must be caught in session write handler
		// http://us.php.net/manual/en/function.session-set-save-handler.php
		try
		{
			$expire=time()+$this->getTimeout();
			$url=isset($_GET['r'])?$_GET['r']:"";
			$db=$this->getDbConnection();
			$sql="SELECT id FROM {$this->sessionTableName} WHERE id=:id";
			if($db->createCommand($sql)->bindValue(':id',$id)->queryScalar()===false){
				$sql="INSERT INTO {$this->sessionTableName} (id, data, expire , url) VALUES (:id, :data, $expire,:url)";
				$db->createCommand($sql)->bindValue(':id',$id)->bindValue(':data',$data)->bindValue(':url',$url)->execute();
			}
			else{
				if($this->_orig_data == $data){
					$sql="UPDATE {$this->sessionTableName} SET expire=$expire WHERE id=:id";
					$db->createCommand($sql)->bindValue(':id',$id)->execute();
				}else{
					$sql="UPDATE {$this->sessionTableName} SET expire=$expire, data=:data WHERE id=:id";
					$db->createCommand($sql)->bindValue(':id',$id)->bindValue(':data',$data)->execute();
				}
			}
		}
		catch(Exception $e)
		{
			if(YII_DEBUG)
				echo $e->getMessage();
			// it is too late to log an error message here
			return false;
		}
		return true;
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
		$sql="DELETE FROM {$this->sessionTableName} WHERE id=:id";
		$this->getDbConnection()->createCommand($sql)->bindValue(':id',$id)->execute();
		return true;
	}

	/**
	 * Session GC (garbage collection) handler.
	 * Do not call this method directly.
	 * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gcSession($maxLifetime)
	{
		$db=$this->getDbConnection();
		$db->setActive(true);
		$sql="DELETE FROM {$this->sessionTableName} WHERE expire<".time();
		$db->createCommand($sql)->execute();
		return true;
	}
}
