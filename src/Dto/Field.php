<?php
namespace MonitoMkr\Dto;

class Field
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2021-06-29
     * Initial release
     */

	private $property;
	private $type = 'string';
	private $name;
	private $method;
	private $nullMark = '';
	private $restrict;
	private $value = '';

	/**
	* getMethod
	*
	* @return $method
	*/
	public function getMethod()
	{
		return $this->method;
	}
	/**
	* getName
	*
	* @return $name
	*/
	public function getName()
	{
		return $this->name;
	}
	/**
	* getNullMark
	*
	* @return $nullMark
	*/
	public function getNullMark()
	{
		return $this->nullMark;
	}
	/**
	* getProperty
	*
	* @return $property
	*/
	public function getProperty()
	{
		return $this->property;
	}
	/**
	* getRestrict
	*
	* @return $restrict
	*/
	public function getRestrict()
	{
		return $this->restrict;
	}
	/**
	* getType
	*
	* @return $type
	*/
	public function getType()
	{
		return $this->type;
	}
	/**
	* getValue
	*
	* @return $value
	*/
	public function getValue()
	{
		return $this->value;
	}
	/**
	 * setMethod
	 *
	 * @param $method
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}
	/**
	 * setName
	 *
	 * @param $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	/**
	 * setNullMark
	 *
	 * @param $nullMark
	 */
	public function setNullMark($nullMark)
	{
		$this->nullMark = $nullMark;
		return $this;
	}
	/**
	 * setProperty
	 *
	 * @param $property
	 */
	public function setProperty($property)
	{
		$this->property = $property;
		return $this;
	}
	/**
	 * setRestrict
	 *
	 * @param $restrict
	 */
	public function setRestrict($restrict)
	{
		$this->restrict = $restrict;
		return $this;
	}
	/**
	 * setType
	 *
	 * @param $type
	 */
	public function setType(string $type)
	{
        if (strpos($type, '\\') !== false) {
            $type = '\\' . $type;
        }

        switch ($type) {
            case 'oid':
                $type = '\\MongoDB\\BSON\\ObjectId';
                break;
            case 'date':
            case 'datetime':
                $type = '\\MonitoLib\\Type\\DateTime';
                break;
            case 'oid':
                $type = 'string';
                break;
            case 'double':
                $type = 'float';
        }

		$this->type = $type;
		return $this;
	}
	/**
	 * setValue
	 *
	 * @param $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}
}