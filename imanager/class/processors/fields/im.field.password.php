<?php
class FieldPassword implements Fieldinterface
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
	}


	public function render($sanitize=false)
	{
		if(is_null($this->name))
			return false;

		$itemeditor = $this->tpl->getTemplates('field');
		$field = $this->tpl->getTemplate('password', $itemeditor);
		$names = array($this->name, 'password_confirm');
		$labels = array('[[lang/password_field]]', '[[lang/password_confirm_field]]');
		$label_classes = array('label-left', 'label-right');
		$fields = new Template();

		for($i=0;$i<2;$i++)
		{
			$fields->push($this->tpl->render($field, array(
					'label' => !empty($labels[$i]) ? $labels[$i] : '',
					'labelclass' => !empty($label_classes[$i]) ? $label_classes[$i] : '',
					'name' => $names[$i],
					'class' => $this->class,
					'value' => '')
				)
			);
		}
		return $fields;
	}
	protected function sanitize($value){return safe_slash_html_input($value);}
}