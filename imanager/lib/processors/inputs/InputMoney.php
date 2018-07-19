<?php

class InputMoney extends InputText implements InputInterface
{
	/**
	 * @var int
	 */
	protected $maxLen = 255;

	/**
	 * InputMoney constructor.
	 *
	 * @param Field $field
	 */
	public function __construct(Field $field) {
		parent::__construct($field);
	}

	/**
	 * This method checks the field inputs and sets the field contents.
	 * If an error occurs, the method returns an error code.
	 *
	 * @param $value
	 * @param bool $sanitize
	 *
	 * @return int|stdClass
	 */
	public function prepareInput($value, $sanitize = false)
	{
		// Set empty value, the input isn't required
		if(empty($value) && !$this->required) {
			$this->values->value = '';
			return $this->values;
		}

		// Check input required
		if(($this->required) && empty($value)) {
			return self::ERR_REQUIRED;
		}

		// Only numbers and thousand separators are permitted
		if(!preg_match('/^[0-9\., ]+$/', $value)) {
			return self::ERR_FORMAT;
		}

		// Change value into float format
		$this->values->value = $this->toFloat($value);

		// String format has wiped the value?
		if(!$this->values->value) { return self::ERR_FORMAT; }

		// Check min value length
		if($this->minLen > 0) {
			if(mb_strlen($this->values->value) < (int) $this->minLen) { return self::ERR_MIN_VALUE; }
		}

		// Check max value length
		if($this->maxLen > 0) {
			if(mb_strlen($this->values->value) > (int) $this->maxLen) { return self::ERR_MAX_VALUE; }
		}

		return $this->values;
	}

	/**
	 * The method that is called when initiating item content
	 * and is relevant for setting the field content.
	 * However, since we do not require any special formatting
	 * of the output, we can accept the value 1 to 1 here.
	 *
	 * @return stdClass
	 */
	public function prepareOutput() { return $this->values; }

	/**
	 * The method used for sanitizing.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function toFloat($str)
	{
		if(strstr($str, ",")) {
			// replace dots and spaces (thousand seps) with blancs
			$str = str_replace('.', '', $str);
			$str = str_replace(' ', '', $str);
			// replace ',' with '.'
			$str = str_replace(',', '.', $str);
		}

		// Search for a number that may contain '.'
		if(preg_match('#([0-9\.]+)#', $str, $match)) {
			return floatval($match[0]);
		} else {
			return floatval($str);
		}
	}
}