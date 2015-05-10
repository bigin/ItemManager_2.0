<?php

interface Fieldinterface
{
	const PREFIX = 'custom-';

	public function render($sanitize=false);

	public function getConfigFieldtype();
}