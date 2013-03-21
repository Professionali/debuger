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
 * Интерфейс плагина
 */
interface Debug_Plugin_Interface {

	/**
	 * Конструктор
	 *
	 * @param Debug_View_Interface $view
	 */
	public function __construct(Debug_Processor $processor, Debug_View_Interface $view);

	/**
	 * Подходил ли данный плагин под отображение переменной
	 *
	 * @param mixed $var
	 *
	 * @return boolean
	 */
	public function is($var);

	/**
	 * Отобразить переменную
	 *
	 * @param mixed $var
	 * @param integer $tab
	 *
	 * @return string
	 */
	public function format($var, $tab=0);

}