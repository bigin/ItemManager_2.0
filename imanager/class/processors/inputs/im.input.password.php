<?php
class InputPassword implements Inputinterface
{
	protected $values;
	protected $field;

	public function __construct(Field $field)
	{
		$this->field = $field;
		$this->values = new stdClass();
		$this->values->value = null;
		$this->values->salt = null;
	}

	public function prepareInput($value)
	{
		if(empty($value))
		{
			$this->values->salt = '';
			$this->values->value = '';
			return $this->values;
		}
		$this->values->salt = $this->randomString();
		$this->values->value = sha1($value . $this->values->salt);
		return $this->values;
	}

	public function prepareOutput(){return $this->values;}

	function randomString($length = 10)
	{
		$characters = '0123456*789abcdefg$hijk#lmnopqrstuvwxyzABC+EFGHIJKLMNOPQRSTUVW@XYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for($i = 0; $i < $length; $i++)
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		return $randomString;
	}
}