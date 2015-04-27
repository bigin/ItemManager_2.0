<?php
class FieldChunk implements Fieldinterface
{
	public $properties;
	protected $tpl;

	public function __construct(ImTplEngine $tpl)
	{
		$this->tpl = $tpl;
		$this->name = null;
		$this->class = null;
		$this->id = null;
		$this->value = null;
		$this->style = null;
	}


	public function render($sanitize=false)
	{
		if(is_null($this->name))
			return false;

		$output = new Template();

		$output->content = $this->value;
		return  $output;
	}
}