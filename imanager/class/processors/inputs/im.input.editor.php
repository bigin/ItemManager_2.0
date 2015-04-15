<?php
class InputEditor implements Inputinterface
{
	protected $values;
	protected $field;

	public function __construct(Field $field)
	{
		$this->field = $field;
		$this->values = new stdClass();
		$this->values->value = null;
	}

	public function prepareInput($value)
	{
		$this->values->value = $value;
		return $this->values;
	}

	public function prepareOutput(){return $this->values;}

}