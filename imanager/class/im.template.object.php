<?php


/**
 * Class Template
 *
 * Anatomie of template file name:
 * name.membership.im.tpl
 *
 */
class Template
{
	public $name;
	protected $file;
	protected $filename;
	protected $member;
	public $content;

	public function __construct()
	{
		$this->name = '';
		$this->file = '';
		$this->filename = '';
		$this->content = (string) '';
	}

	public function get($name){return $this->$name;}

	public function set($key, $val)
	{
		$key = strtolower($key);
		if($key == 'name')
		{
			$base = basename($val, '.im.tpl');
			$strp = strpos($base, '.');
			$name = substr($base, 0, $strp);
			$member = substr($base, $strp+1);
			$this->name = $name;
			$this->member = $member;
			if(file_exists(IM_TEMPLATE_DIR.$name.'.'.$member.IM_TEMPLATE_FILE_SUFFIX))
			{
				$this->file = IM_TEMPLATE_DIR.$name.'.'.$member.IM_TEMPLATE_FILE_SUFFIX;
				$this->filename = $name.'.'.$member.IM_TEMPLATE_FILE_SUFFIX;
			}
		} else
			$this->$key = $val;
	}

	public function push(Template $val){ $this->content .= $val->content;}

}