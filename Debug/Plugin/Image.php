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
 * Плагин отображения ресурсов gd
 */
class Debug_Plugin_Image extends Debug_Plugin_Abstract implements Debug_Plugin_Interface {

	/**
	 * Подходил ли данный плагин под отображение переменной
	 * @see Debug_Plugin_Interface::is()
	 *
	 * @param mixed $var
	 *
	 * @return boolean
	 */
	public function is($arg) {
		return is_resource($arg) && get_resource_type($arg)=='gd';
	}

	/**
	 * Отобразить переменную
	 * @see Debug_Plugin_Interface::format()
	 *
	 * @param mixed $var
	 * @param integer $tab
	 *
	 * @return string
	 */
	public function format($var, $tab=0) {
		return $this->view->render('resource', 'resource', ' '.$this->view->image($var));
	}
}