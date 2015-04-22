<?php

interface Inputinterface
{
	const ERR_REQUIRED  = 1;
	const ERR_MIN_VALUE = 2;
	const ERR_MAX_VALUE = 3;
	const ERR_SANITIZE  = 4;
	const ERR_UNDEFINED = 5;

	public function __construct(Field $field);

	public function prepareInput($value);

	public function prepareOutput();
}