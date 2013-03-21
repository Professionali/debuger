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
 * Представление для вывода в html
 */
class Debug_View_Html extends Debug_View_Abstract implements Debug_View_Interface {

	/**
	 * Стили
	 *
	 * @var array
	 */
	private $style = array (
		'boolean'  => 'color: #F00;',
		'integer'  => 'color: #00F;',
		'float'    => 'color: #609;',
		'string'   => 'color: #090;',
		'array'    => 'color: #099;',
		'object'   => 'color: #07b;',
		'resource' => 'color: #D6F;',
		'null'     => 'color: #000;',
		'unknown'  => 'color: #000;',
		'key'      => 'color: #666;',
		'value'    => 'color: #000;',
		'nonprint' => 'color: #666;',
		'link'     => 'border-bottom: 1px dashed #999999;color: #999999;text-decoration: none;cusor:pointer;',
		'nolink'  => 'border-bottom: 1px dashed #999999;color: #999999;text-decoration: none;',
		'bold'     => 'font-weight: bold;',
	);

	/**
	 * Экранирование строк
	 *
	 * @param string $val
	 */
	public function escape($val) {
		static $symbols = array('\n'=>"\n", '\r'=>"\r", '\t'=>"\t");
		$val = htmlspecialchars($val);
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
	private function getStyle($type) {
		return isset($this->style[$type]) ? ' style="'.$this->style[$type].'"' : '';
	}

	/**
	 * Отобразить элемент
	 *
	 * @param string $type
	 * @param string $param
	 * @param string $additional
	 */
	public function render($type, $additional, $param=null) {
		return '<span'.$this->getStyle($type).'>'.$additional.'</span>'.(is_string($param) ?'<span'.$this->getStyle('value').'>'.$param.'</span>':'');
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $tab
	 */
	public function newLine($text ,$tab=0) {
		return "</br>".str_repeat("\t", $tab).$text;
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 */
	public function showDump($text) {
		return '<pre style="border: 1px dashed #ddd; padding: 5px; margin: 2px; text-align: left; background-color: #f9f9f9;font-size:11px;overflow: auto;">'.$text.'</pre>';
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 */
	public function showError($text, $type) {
		return '<pre style="border: 1px dashed #faa; padding: 5px; margin: 2px; text-align: left; background-color: #fff9f9;font-size:11px;overflow: auto;">'.$text.'</pre>';
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 */
	public function showErrorSilent($text, $type) {
		return '<pre style="border: 1px dashed #ddd; padding: 5px; margin: 2px; text-align: left; background-color: #f9f9f9; color:#aaa;font-size:11px;overflow: auto;">'.$text.'</pre>';
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $id
	 */
	public function getOpenCloseBox($text, &$id=null) {
		static $counter=0;
		$counter++;
		$id = $counter;
		return '<span style="cursor:pointer;" onclick="var e=document.getElementsByClassName(\'debuger_'.$id.'\');if(e)for(var i in e)if(e[i].style)e[i].style.display=(e[i].style.display==\'none\')?\'\':\'none\';">'.$text.'</span>';
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $id
	 * @param unknown_type $decorator
	 */
	public function getCloseBox($text, $id=null, $decorator=false) {
		return '<span style="display:none;" class="debuger_'.$id.'">'.$text.'</span>';
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $text
	 * @param unknown_type $id
	 * @param unknown_type $decorator
	 */
	public function getOpenBox($text, $id=null, $decorator=false) {
		return '<span style="" class="debuger_'.$id.'">'.$text.'</span>';
	}

	/**
	 * TODO
	 *
	 * @param unknown_type $image
	 */
	public function image($image) {
		ob_start();
		imagepng($image);
		$text = ob_get_clean();
		return 'gd image['.imagesx($image).'×'.imagesy($image).'] <img src="data:image/png;base64,'.base64_encode($text).'" style="vertical-align:top;" alt="IE not support" border="0" />';
	}

	/**
	 * Форматировать ссылки
	 *
	 * @param unknown_type $file
	 * @param unknown_type $line
	 * @param unknown_type $text
	 */
	public function formatLink($file, $line, $text) {
		if($file=='unknown' || $line=='unknown' || is_null($this->processor->getLinkTemplate())) {
			return $this->render('nolink', $text);
		} else {
			if(strpos($file, $this->processor->getBasePach())===0) {
				$file = substr($file, strlen($this->processor->getBasePach())+1);
			}
			$link = sprintf($this->processor->getLinkTemplate(), $file, $line);
			return $this->render('link', "<a href=\"{$link}\">{$text}</a>");
		}
	}
}
