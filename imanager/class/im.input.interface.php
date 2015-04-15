<?php

interface Inputinterface
{
	public function __construct(Field $field);

	public function prepareInput($value);

	public function prepareOutput();
}