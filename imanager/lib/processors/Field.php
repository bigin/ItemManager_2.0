<?php

class Field
{
	protected static $column;
	public function __construct($n=null){if(!is_null($n)) self::$column = $n;}
}