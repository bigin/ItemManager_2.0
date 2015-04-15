<?php

class Category
{
	protected $id;
	protected $file;
	protected $filename;
	//public $xmlobject;

	public function __construct()
	{
		$this->id = null;
		$this->file = '';
		$this->filename = '';

		$this->position = null;
		$this->name = '';
		$this->created = time();
		$this->updated = '';
	}

	public function get($name){return isset($this->$name) ? $this->$name : false;}

	public function set($key, $val)
	{

		$key = strtolower($key);
		$val = safe_slash_html_input($val);
		// id is readonly
		if(!in_array($key, array('name', 'position', 'created', 'updated')))
			return false;
		$this->$key = $val;
	}

	public function setProtectedParams($id)
	{
		if(!is_numeric($id)) return false;
		$this->id = $id;
		$this->file = IM_CATEGORY_DIR.$id.IM_CATEGORY_FILE_SUFFIX;
		$this->filename = $id.IM_CATEGORY_FILE_SUFFIX;
		return true;
	}


	public function save()
	{
		// edit category
		if(!is_null($this->id) && $this->id > 0)
		{
			$xml = simplexml_load_file($this->file);
			$this->updated = time();

			$xml->id = intval($this->id);
			$xml->name = (string) $this->name;
			$xml->position = !is_null($this->position) ? intval($this->position) : intval($this->id);
			$xml->created = intval($this->created);
			$xml->updated = intval($this->updated);

			return $xml->asXml($this->file);
		}

		// new category
		else
		{
			$c = new ImCategory();
			$c->init();

			$this->id = max(array_keys($c->categories))+1;
			$this->file = IM_CATEGORY_DIR.$this->id.IM_CATEGORY_FILE_SUFFIX;

			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><category></category>');

			$xml->name = $this->name;
			$xml->position = !is_null($this->position) ? $this->position : $this->id;
			$xml->created = $this->created;
			$xml->updated = $this->updated;

			return $xml->asXml($this->file);
		}
	}
}