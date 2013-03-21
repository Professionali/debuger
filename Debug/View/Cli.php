<?php
/**
 * Обработчик ошибок и вывод дампа переменных
 *
 * @author    Valetin Gernovich <gernovich@ya.ru>
 * @copyright Copyright (c) 2011, Valetin Gernovich. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   0.2
 */

/**
 * Представление для коммандной строки
 */
class Debug_View_Cli extends Debug_View_Abstract implements Debug_View_Interface {

	const ATTR_NORMAL = 0;
	const ATTR_BRIGHT = 1;
	const ATTR_UNDERLINE = 4;
	const ATTR_BLINK = 5;
	const ATTR_REVERSE = 7;
	const ATTR_HIDDEN = 8;

	const FG_BLACK = 30;
	const FG_RED = 31;
	const FG_GREEN = 32;
	const FG_YELLOW = 33;
	const FG_BLUE = 34;
	const FG_MAGENTA = 35;
	const FG_CYAN = 36;
	const FG_WHITE = 37;

	const BG_BLACK = 40;
	const BG_RED = 41;
	const BG_GREEN = 42;
	const BG_YELLOW = 44;
	const BG_BLUE = 44;
	const BG_MAGENTA = 45;
	const BG_CYAN = 46;
	const BG_WHITE = 47;

	const PREFIX = "\x1b[";
	const SUFFIX = "m";
	const SEPARATOR = ';';
	const END_COLOR = "\x1b[m";

	/**
	 * Стили
	 *
	 * @var array
	 */
	private $style = array (
		'boolean'  => array(self::FG_RED),
		'integer'  => array(self::FG_BLUE, self::ATTR_BRIGHT),
		'float'    => array(self::FG_BLUE),
		'string'   => array(self::FG_GREEN),
		'array'    => array(self::FG_CYAN),
		'object'   => array(self::FG_CYAN, self::ATTR_BRIGHT),
		'resource' => array(self::FG_MAGENTA, self::ATTR_BRIGHT),
		'null'     => array(self::FG_MAGENTA),
		'unknown'  => array(self::FG_WHITE),
		'key'      => array(self::FG_WHITE),
		'value'    => array(self::FG_WHITE, self::ATTR_BRIGHT),
		'nonprint' => array(self::FG_BLACK, self::ATTR_BRIGHT),
		'link'     => array(self::FG_WHITE, self::ATTR_BLINK, self::ATTR_UNDERLINE),
		'nolink'   => array(self::FG_WHITE, self::ATTR_BLINK, self::ATTR_UNDERLINE),
		'bold'     => array(self::FG_RED, self::ATTR_BRIGHT),
	);

	/**
	 * Экранирование строк
	 *
	 * @param string $val
	 */
	public function escape($val) {
		static $symbols = array('\n'=>"\n", '\r'=>"\r", '\t'=>"\t");
		$self = $this;
		$val = preg_replace_callback('/[[:cntrl:]]/', function ($val) use ($symbols, $self) {
			if (array_search($val[0], $symbols)) {
				return $self->render('nonprint', array_search($val[0], $symbols));
			} else {
				return $self->render('nonprint', '\\x'.dechex(ord($val[0])));
			}
		}, $val);
		return $val;
	}

	/**
	 * Установить стиль
	 *
	 * @param string $style
	 *
	 * @return string
	 */
	private function getStyle($type=null) {
		return isset($this->style[$type]) ? $this->color($this->style[$type]) : self::END_COLOR;
	}

	/**
	 * Установить цвет
	 *
	 * @param string $style
	 *
	 * @return string
	 */
	private function color($color=array()) {
		$color = (array) $color;
		return self::PREFIX.implode(self::SEPARATOR, $color).self::SUFFIX;
	}

	/**
	 * Отобразить элемент
	 *
	 * @param string $type
	 * @param string $param
	 * @param string $additional
	 */
	public function render($type='unknown', $additional='', $param=null) {
		return $this->getStyle().$this->getStyle($type).$additional.(is_string($param)?''.$this->getStyle('value').$param:'').$this->getStyle();
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $tab
	 */
	public function newLine($text ,$tab=0) {
		return "\n".str_repeat("\t", $tab).$text;
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 */
	public function showDump($text) {
		return $text."\n";
	}
	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 */
	public function showError($text, $type) {
		$messages = explode("\n", $text);
		$len = 0;
		foreach ($messages as $row) if (self::termLen($row) > $len) $len = self::termLen($row);
		foreach ($messages as &$row) {
			$row = $this->color(self::FG_RED).'║ '.self::END_COLOR.self::str_pad($row, $len, ' ', STR_PAD_RIGHT).$this->color(self::FG_RED).' ║'.self::END_COLOR;
		}
		$header = $this->color(array(self::BG_RED, self::FG_BLACK)).' '.Debug_Processor::$error_type[$type].' '.self::END_COLOR.$this->color(self::FG_RED);
		$header_len = self::termLen($header);
		$header_pad = 2;
		$messages = array_merge(array($this->color(self::FG_RED)."╔".str_repeat("═", $len-$header_pad-$header_len+2).$header.str_repeat("═", $header_pad)."╗".self::END_COLOR), $messages);
		$messages = array_merge($messages, array($this->color(self::FG_RED)."╚".str_repeat("═", $len+2)."╝".self::END_COLOR));
		return implode("\n", $messages)."\n";
	}

	/**
	 * Длина без учета терминальных символов, с учетом табуляции и в кодировке utf8
	 */
	private static function termLen($str) {
		$str = preg_replace('~'.preg_quote(self::PREFIX,'~').'[^'.preg_quote(self::SUFFIX,'~').']*'.preg_quote(self::SUFFIX,'~').'~is', '', $str);
		$str = str_replace("\t", str_repeat(" ", 6), $str);
		return self::strlen($str);
	}
	/**
	 * Дополняет строку другой строкой до заданной длины
	 *
	 * @param string|null   $string  исходная строка
	 * @param integer|digit $length  длинна дополнения
	 * @param string        $pad_str чем дополнять, gо умолчанию пробел
	 * @param integer       $type    Необязательный аргумент может иметь значение STR_PAD_RIGHT, STR_PAD_LEFT или STR_PAD_BOTH, по умолчанию STR_PAD_RIGHT
	 *
	 * @return string|boolean|null
	 */
	private static function str_pad($string, $length, $pad_str = ' ') {
		$pad_len = $length-self::termLen($string);
		if ($pad_len <0) $pad_len = 0;
		return $string.str_repeat($pad_str,  $pad_len);
	}


	/**
	 * Implementation strlen() function for UTF-8 encoding string.
	 *
	 * @param string|null $string строка
	 *
	 * @return integer|boolean|null вернет false в случаи ошибки
	 */
	private static function strlen($string) {
		if (is_null($string)) {
			return $string;
		}
		return strlen(utf8_decode($string));
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 */
	public function showErrorSilent($text, $type) {
		return "{$text}\n";
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $id
	 */
	public function getOpenCloseBox($text, $id=null) {
		return $text;
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $id
	 * @param unknown_type $decorator
	 */
	public function getCloseBox($text, $id=null, $decorator=false) {
		if ($decorator) {
			return '';
		}
		return $text;
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $id
	 * @param unknown_type $decorator
	 */
	public function getOpenBox($text, $id=null, $decorator=false) {
		if ($decorator) {
			return '';
		}
		return $text;
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $image
	 */
	public function image($image) {
		return 'gd image['.imagesx($image).'×'.imagesy($image).']';
	}
	/**
	 * Форматировать ссылки
	 *
	 * @param unknown_type $file
	 * @param unknown_type $line
	 * @param unknown_type $text
	 */
	public function formatLink($file, $line, $text) {
		return $this->render('nolink', $text);
	}
}