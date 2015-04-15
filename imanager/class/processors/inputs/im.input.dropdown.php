<?php
class InputDropdown implements Inputinterface
{
	protected $values;
	protected $field;

	public function __construct(Field $field)
	{
		$this->field = $field;
		$this->values = new stdClass();
		$this->values->value = null;
	}

	public function prepareInput($value, $sanitize=false)
	{
		$this->values->value = !$sanitize ? $value : $this->sanitize($value);
		return $this->values;
	}

	public function prepareOutput(){return $this->values;}

	protected function sanitize($value){return safe_slash_html_input($value);}
}