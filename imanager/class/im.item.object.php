<?php
class Item
{
	protected $categoryid;
	protected $id;
	protected $file;
	protected $filename;

	public function __construct($catid)
	{
		$this->categoryid = intval($catid);

		$this->id = null;
		$this->file = '';
		$this->filename = '';

		$this->name = '';
		$this->label = '';
		$this->position = null;
		$this->active = 0;

		$this->created = time();
		$this->updated = null;

		$this->fields = new stdClass();
		// field arts object array
		$fc = new ImFields();
		$fc->init($catid);
		foreach($fc->fields as $name => $value)
			$this->fields->$name = $value;

	}



	public function getNextId()
	{
		// no category is selected, return false
		if(!$this->categoryid) return false;

		$ids = array();
		// check item file exists return back
		if(glob(IM_ITEM_DIR.'*.'.$this->categoryid.IM_ITEM_FILE_SUFFIX))
		{
			foreach (glob(IM_ITEM_DIR.'*.'.$this->categoryid.IM_ITEM_FILE_SUFFIX) as $file)
			{
				$base = basename($file, IM_ITEM_FILE_SUFFIX);
				$strp = strpos($base, '.');
				$ids[] = substr($base, 0, $strp);
			}
			return !empty($ids) ? max($ids)+1 : false;
		}
		// ok this may the first item for this category
		if(!file_exists(IM_ITEM_DIR.'1.'.$this->categoryid.IM_ITEM_FILE_SUFFIX))
			return 1;
	}


	public function set($key, $val)
	{
		$this->$key = $val;
	}



	public function get($key)
	{
		if(isset($this->$key)) return $this->$key;

		return false;
	}


	public function save()
	{
		// new file
		if(is_null($this->id) && !file_exists(IM_ITEM_DIR.$this->id.'.'.$this->categoryid.IM_ITEM_FILE_SUFFIX))
		{
			$this->id = $this->getNextId();
			$this->file = IM_ITEM_DIR.$this->id.'.'.$this->categoryid.IM_ITEM_FILE_SUFFIX;
			$this->filename = $this->id.'.'.$this->categoryid.IM_ITEM_FILE_SUFFIX;

			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><item></item>');

			$xml->categoryid = $this->categoryid;
			$xml->id = $this->id;
			$xml->name = $this->name;
			$xml->label = $this->label;
			$xml->position = !is_null($this->position) ? $this->position : $this->id;
			$xml->active = $this->active;

			$xml->created = $this->created;
			$xml->updated = $this->updated;

			$data = $this->getFieldsDataToSave();

			if(!empty($data['ids']))
			{
				foreach($data['ids'] as $key => $val)
				{
					$xml->field[$key]->id = $val;

					if(!empty($this->fields->$data['names'][$key]->value))
					{
						$inputClassName = 'Input'.$data['types'][$key];
						$InputType = new $inputClassName($this->fields->$data['names'][$key]);

						//$input = $InputType->prepareInput($this->fields->$data['names'][$key]->value);
						$output = $InputType->prepareOutput();
						$input = new stdClass();

						foreach ($output as $inputkey => $inputval)
							$input->$inputkey = $this->fields->$data['names'][$key]->$inputkey;

						foreach($input as $inputkey => $inputvalue)
						{
							if(!is_array($inputvalue))
							{
								$xml->field[$key]->$inputkey = $inputvalue;
							} else
							{
								foreach($inputvalue as $inputvalue_key => $inputvalue_value)
								{
									$xml->field[$key]->{$inputkey}[] = $inputvalue_value;
								}
							}
						}
					}
				}
			} else
				$xml->field = '';

			return $xml->asXml($this->file);

		// overwrite file
		} elseif(!is_null($this->id))
		{
			$xml = simplexml_load_file($this->file);

			$xml->categoryid = $this->categoryid;
			$xml->id = $this->id;
			$xml->name = $this->name;
			$xml->label = $this->label;
			$xml->position = !is_null($this->position) ? $this->position : $this->id;
			$xml->active = $this->active;

			$xml->created = $this->created;
			$xml->updated = time();

			$data = $this->getFieldsDataToSave();

			if(!empty($data['ids']))
			{
				unset($xml->field);
				foreach($data['ids'] as $key => $val)
				{
					$xml->field[$key]->id = $val;

					if(!empty($this->fields->$data['names'][$key]->value))
					{
						$inputClassName = 'Input'.$data['types'][$key];
						$InputType = new $inputClassName($this->fields->$data['names'][$key]);

						$output = $InputType->prepareOutput();
						$input = new stdClass();

						foreach ($output as $inputkey => $inputval)
							$input->$inputkey = $this->fields->$data['names'][$key]->$inputkey;

						foreach($input as $inputkey => $inputvalue)
						{
							if(!is_array($inputvalue))
							{
								$xml->field[$key]->$inputkey = $inputvalue;
							} else
							{
								foreach($inputvalue as $inputvalue_key => $inputvalue_value)
								{
									$xml->field[$key]->{$inputkey}[] = $inputvalue_value;
								}
							}
						}
					}
				}
			} else
				$xml->field = '';

			return $xml->asXml($this->file);

		}

		return false;
	}

	// todo: Wird die hier noch verwendet?
	public function getFieldValue($fieldid)
	{
		foreach($this->fields as $key => $val)
			var_dump($val);
	}

	protected function getFieldsDataToSave()
	{
		return ImFields::getFieldsSaveInfo($this->categoryid);
	}
}