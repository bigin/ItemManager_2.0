<?php

trait ImObserver
{

	public $hook = array();
	/**
	 * check if $GLOBALS['hook'] isset and is array
	 */
	public function __construct()
	{
		/*if( !isset( $GLOBALS['hook'] ) && !is_array( $GLOBALS['hook'] ) )
		{
			return;
		}*/
	}

	/**
	 * save hook function in $GLOBALS['hook']
	 * @param String, function
	 */
	public function watch($channel, $function)
	{
		if(!isset($this->hook[$channel]))
		{
			$this->hook[$channel] = array();
		}
		array_push($this->hook[$channel], $function);
		/*if( !isset( $GLOBALS['hook'][$channel] ) ){

			$GLOBALS['hook'][$channel] = array();
		}
		array_push($GLOBALS['hook'][$channel], $func);*/
	}

	/**
	 * loop through $GLOBALS['hook'] and call hook functions
	 * @param String
	 */
	private function subscribe($channel, $obj)
	{
		if(!isset($this->hook[$channel])) return;

		foreach($this->hook[$channel] as $function)
			$function($obj);
		/*if( !isset( $GLOBALS['hook'][$channel] ) )
			return;

		foreach( $GLOBALS['hook'][$channel] as $func )
		{
			$func();
		}*/

	}


}