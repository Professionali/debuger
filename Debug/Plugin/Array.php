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
 * Плагин отображения массивов
 */
class Debug_Plugin_Array extends Debug_Plugin_Abstract implements Debug_Plugin_Interface {

	/**
	 * Максимальный уровень вложенности
	 */
	const MAX_LEVEL = 10;

	/**
	 * Уровень при котором нужно сворачивать данные
	 */
	const COLLAPSE_LEVEL = 2;

	/**
	 * Подходил ли данный плагин под отображение переменной
	 * @see Debug_Plugin_Interface::is()
	 *
	 * @param mixed $var
	 *
	 * @return boolean
	 */
	public function is($var) {
		return is_array($var);
	}

	/**
	 * Отобразить переменную
	 * TODO добавить обработку рекурсии
	 * @see Debug_Plugin_Interface::format()
	 *
	 * @param mixed $var
	 * @param integer $tab
	 *
	 * @return string
	 */
	public function format($var, $tab=0) {
		if (self::MAX_LEVEL >= $tab+1) {
			$tab++;
			$param = array();
			foreach($var as $key=>$value) {
				$param[] = $this->view->render('key', (is_int($key)?"{$key}":"'{$key}'")." => ").$this->processor->dump($value, $tab);
			}
			$view = $this->view;
			$render = $this->view->render('array', '', ' (');
			$render .= implode(', ', array_map(function($val) use ($tab, $view) {
				return $view->newLine($val, $tab);
			}, $param));
			$tab--;
			$render .= $this->view->newLine(')', $tab);

			$id=null;
			$additional = $this->view->getOpenCloseBox('array['.count($var).']', $id);

			if(self::COLLAPSE_LEVEL >= $tab) {
				$render = $this->view->getOpenBox($render, $id);
				$render .= $this->view->getCloseBox(' (...)', $id, true);
			} else {
				$render = $this->view->getCloseBox($render, $id);
				$render .= $this->view->getOpenBox(' (...)', $id, true);
			}
			return $this->view->render('array', $additional, $render);
		} else {
			return $this->view->render('array', 'array['.count($var).']', ' (...)');
		}
	}
}