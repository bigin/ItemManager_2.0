<?php
class ImMsgReporter
{
	private static $_msgs=array();
	private static $error=false;

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

	public static function msgs(){return (self::$_msgs);}
	public static function isError(){return (self::$error);}
}
?>
