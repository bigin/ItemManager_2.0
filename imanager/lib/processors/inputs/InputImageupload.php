<?php
class InputImageupload implements InputInterface
{
	protected $values;
	protected $field;

	public function __construct(Field $field)
	{
		$this->field = $field;
		$this->values = new stdClass();
		$this->values->value = null;

		$this->values->imagename = array();

		$this->values->imagepath = array();
		$this->values->imagefullpath = array();
		$this->values->imageurl = array();
		$this->values->imagefullurl = array();
		$this->values->imagetitle = array();

		/*$this->values->thumbpath = array();
		$this->values->thumbfullpath = array();
		$this->values->thumburl = array();
		$this->values->thumbfullurl = array();*/

		$this->positions = array();
		$this->titles = array();
	}


	public function prepareInput($value, $sanitize=false)
	{
		if(!file_exists($value))
			return $this->values;

		$temp_arr = array();


		if(empty($this->positions) && file_exists($value.'config.xml'))
		{
			$xml = simplexml_load_file($value.'config.xml');
			for($i = 0; $i < count($xml->image); $i++)
			{
				$this->positions[(int) $xml->image[$i]->position] = (string) $xml->image[$i]->name;
				$this->titles[(int) $xml->image[$i]->position] = (string) $xml->image[$i]->titles;
			}
		}
		$i = 0;
		foreach(glob($value.'*') as $file)
		{
			if(is_dir($file) || 'xml' == pathinfo($file, PATHINFO_EXTENSION)) continue;

			$base = basename($file);
			$basedir = basename($value);

			$poskey = $i;
			$title = '';
			if(!empty($this->positions))
			{
				$poskey = array_search($base, $this->positions);

				//if(array_key_exists($i, $this->titles))
				$title = $this->titles[$poskey];
			}


			$temp_arr[$i] = new stdClass();

			$temp_arr[$i]->imagename = $base;
			$temp_arr[$i]->position = (int) $poskey;
			$temp_arr[$i]->imagepath = $value;
			$temp_arr[$i]->imagefullpath = $value.$base;
			$temp_arr[$i]->imageurl = 'data/uploads/imanager/'.$basedir.'/';
			$temp_arr[$i]->imagefullurl = 'data/uploads/imanager/'.$basedir.'/'.$base;
			$temp_arr[$i]->imagetitle = $title;

			/*$temp_arr[$i]->thumbpath = $value.'thumbnail/';
			$temp_arr[$i]->thumbfullpath = $value.'thumbnail/'.$base;
			$temp_arr[$i]->thumburl = $this->getFullUrl().'/data/uploads/imanager/'.$basedir.'/thumbnail/'.$base;
			$temp_arr[$i]->thumbfullurl = $this->getFullUrl().'/data/uploads/imanager/'.$basedir.'/thumbnail/'.$base;*/

			$i++;
		}

		usort($temp_arr, array($this, 'sortObjects'));

		$this->values->value = $value;

		foreach($temp_arr as $key => $val)
		{
			$this->values->imagename[] = $temp_arr[$key]->imagename;
			$this->values->imagepath[] = $temp_arr[$key]->imagepath;
			$this->values->imagefullpath[] = $temp_arr[$key]->imagefullpath;
			$this->values->imageurl[] = $temp_arr[$key]->imageurl;
			$this->values->imagefullurl[] = $temp_arr[$key]->imagefullurl;
			$this->values->imagetitle[] = $temp_arr[$key]->imagetitle;

			/*$this->values->thumbpath[] = $temp_arr[$key]->thumbpath;
			$this->values->thumbfullpath[] = $temp_arr[$key]->thumbfullpath;
			$this->values->thumburl[] = $temp_arr[$key]->thumburl;
			$this->values->thumbfullurl[] = $temp_arr[$key]->thumbfullurl;*/

		}
		// delete empty config file
		if($i <= 0 && file_exists($value.'config.xml')) {unlink($value.'config.xml');}

		return $this->values;
	}


	public function prepareOutput(){return $this->values;}


	protected function sanitize($value){return imanager('sanitizer')->text($value);}


	private function sortObjects($a, $b)
	{
		$a = $a->position;
		$b = $b->position;

		if($a == $b) {return 0;}
		else
		{
			if($b > $a) {return -1;}
			else {return 1;}
		}
	}

	protected function getFullUrl()
	{
		return IM_SITE_URL;
		/*$https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0 ||
			!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
		return
			($https ? 'https://' : 'http://').
			(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
			(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
				($https && $_SERVER['SERVER_PORT'] === 443 ||
				$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT'])));*/
	}
}