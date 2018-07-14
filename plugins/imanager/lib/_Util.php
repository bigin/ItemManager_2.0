<?php
//namespace Imanager;

class Util
{
	/**
	 * Function for checking session status
	 *
	 * @return bool
	 */
	public static function is_session_started()
	{
		if(php_sapi_name() !== 'cli') {
			if (version_compare(phpversion(), '5.4.0', '>='))
			{
				return session_status() === PHP_SESSION_ACTIVE ? true : false;
			} else
			{
				return session_id() === '' ? false : true;
			}
		}
		return false;
	}

	public static function reparse_url($parsed_url, $imcat)
	{
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
		$host     = isset($parsed_url['host'])   ? $parsed_url['host'] : '';
		$path     = isset($parsed_url['path'])   ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query'])  ? '?'.$parsed_url['query'] : '';
		$pairs = explode('&', $query);
		foreach($pairs as $pair)
		{
			$part = explode('=', $pair);
			if($part[0] == 'page')
			{
				return ($scheme.$host.$path.'?id=imanager&cat='. $imcat->current_category().'&page=');
			}
		}
		return ;
	}

	public static function dataLog($data, $file = '')
	{
		$filename = empty($file) ? GSDATAOTHERPATH.'logs/imlog_'.date('Ym').'.txt' : GSDATAOTHERPATH.'logs/'.$file.'.txt';
		if (!$handle = fopen($filename, 'a+'))
		{
			return;
		}
		$datum = date('d.m.Y - H:i:s', time());
		if (!fwrite($handle, '[ '.$datum.' ]'. ' ' . print_r($data, true) . "\r\n")) {
			return;
		}
		fclose($handle);
	}

	public static function preformat($data){echo '<pre>'.print_r($data, true).'</pre>';}

	public static function isTimestamp($string){return (1 === preg_match( '~^[1-9][0-9]*$~', $string ));}

	/**
	 * Check the PHP_INT_SIZE constant. It'll vary based on the size of the register (i.e. 32-bit vs 64-bit)
	 * In 32-bit systems PHP_INT_SIZE should be 4, for 64-bit it should be 8
	 *
	 * @return int
	 */
	public static function getIntSize() {return PHP_INT_SIZE;}

	/**
	 * Function to compute the unsigned crc32 value.
	 * PHP crc32 function returns int which is signed, so in order to get the correct crc32 value
	 * we need to convert it to unsigned value.
	 *
	 * NOTE: it produces different results on 64-bit compared to 32-bit PHP system
	 *
	 * @param $str - String to compute the unsigned crc32 value.
	 * @return $var - Unsinged inter value.
	 */
	public static function computeUnsignedCRC32($str)
	{
		sscanf(crc32($str), "%u", $var);
		return $var;
	}


	public static function redirect($url, $flag = true, $statusCode = 303)
	{
		header('Location: ' . htmlspecialchars($url), $flag, $statusCode);
		die();
	}
}