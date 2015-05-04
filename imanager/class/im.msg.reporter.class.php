<?php
class ImMsgReporter
{
	private static $_msgs=array();
	private static $error_code;
	private static $error=false;

	const ERR_UPDATED_BY_PROCESS = 20;
	/*const ERR_MIN_VALUE  = 2;
	const ERR_MAX_VALUE  = 3;
	const ERR_SANITIZE   = 4;
	const ERR_INCOMPLETED= 5;
	const ERR_UNDEFINED  = 6;
	const ERR_COMPARISON = 7;
	const SUCCESS = 10;*/

	public static function setClause($name, array $var=array(), $error=false)
	{
		i18n_merge('imanager') || i18n_merge('imanager','en_US');
		$o = i18n_r('imanager/'.$name);
		if(empty($var))
		{
			if($error) self::setError();
			self::$_msgs[] = $o;
			return;
		}
		foreach($var as $key => $value)
			$o = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $o);
		self::$_msgs[] = $o;
		if($error) self::setError();
	}

	public static function getClause($name, array $var=array())
	{
		i18n_merge('imanager') || i18n_merge('imanager','en_US');
		$o = i18n_r('imanager/'.$name);
		if(empty($var))
			return $o;
		foreach($var as $key => $value)
			$o = preg_replace('%\[\[( *)'.$key.'( *)\]\]%', $value, $o);
		return $o;
	}

	public static function setError(){self::$error=true;}
	public static function setCode($val){self::$error_code = (int) $val; self::$error=true;}

	public static function msgs(){return (self::$_msgs);}
	public static function isError(){return (self::$error);}
	public static function errorCode(){return (self::$error_code);}
}
?>
