<?php
class FieldImageupload implements Fieldinterface
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
		$this->categoryid = null;
		$this->itemid = null;
		$this->timestamp = null;
	}


	public function render($sanitize=false)
	{
		if(is_null($this->name))
			return false;

		$itemeditor = $this->tpl->getTemplates('field');
		$field = $this->tpl->getTemplate('imageupload', $itemeditor);

		$output = $this->tpl->render($field, array(
				'name' => $this->name,
				'class' => $this->class,
				'id' => $this->id,
				'value' => $this->value,
				'scriptdir' => IM_SITE_URL,
				'item-id' => $this->itemid,
				'currentcategory' => $this->categoryid,
				'timestamp' => $this->timestamp,
			), true, array()
		);
		return $output;
	}
}