<?php
namespace MonitoMkr\Type;

use \MonitoLib\App;
use \MonitoLib\Database\Connector;
use \MonitoLib\Exception\BadRequest;
use \MonitoLib\Functions;

class Options
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2021-06-22
     * Initial release
     */

	private $connection;
	private $methods;
	private $dbms;
	private $force = false;
	private $classname;
	private $namespace;
	private $baseUrl;

	/**
	* getConnection
	*
	* @return $connection
	*/
	public function getConnection() : string
	{
		return $this->connection;
	}
	/**
	* getMethods
	*
	* @return $methods
	*/
	public function getMethods()
	{
		return $this->methods;
	}
	/**
	* getDbms
	*
	* @return $dbms
	*/
	public function getDbms() : string
	{
		return $this->dbms;
	}
	/**
	* getForce
	*
	* @return $force
	*/
	public function getForce() : bool
	{
		return $this->force;
	}
	/**
	* getClassname
	*
	* @return $classname
	*/
	public function getClassname() : string
	{
		return $this->classname;
	}
	/**
	* getNamespace
	*
	* @return $namespace
	*/
	public function getNamespace() : string
	{
		return $this->namespace;
	}
	/**
	* getBaseUrl
	*
	* @return $baseUrl
	*/
	public function getBaseUrl() : string
	{
		return $this->baseUrl;
	}
	/**
	 * setConnection
	 *
	 * @param $connection
	 */
	public function setConnection($connection)
	{
		$this->connection = $connection;
		return $this;
	}
	/**
	 * setMethods
	 *
	 * @param $methods
	 */
	public function setMethods($methods)
	{
		$this->methods = $methods;
		return $this;
	}
	/**
	 * setDbms
	 *
	 * @param $dbms
	 */
	public function setDbms($dbms)
	{
		$this->dbms = $dbms;
		return $this;
	}
	/**
	 * setForce
	 *
	 * @param $force
	 */
	public function setForce($force)
	{
		$this->force = $force;
		return $this;
	}
	/**
	 * setClassname
	 *
	 * @param $classname
	 */
	public function setClassname($classname)
	{
		$this->classname = $classname;
		return $this;
	}
	/**
	 * setNamespace
	 *
	 * @param $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}
	/**
	 * setBaseUrl
	 *
	 * @param $baseUrl
	 */
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = $baseUrl;
		return $this;
	}
}
