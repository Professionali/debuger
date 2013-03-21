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
 * Интерфейс представления
 */
interface Debug_View_Interface {

	/**
	 * Конструктор
	 *
	 * @param Debug_Processor $processor
	 */
	public function __construct(Debug_Processor $processor);

	/**
	 * Отобразить элемент
	 *
	 * @param string $type
	 * @param string $param
	 * @param string $additional
	 */
	public function render($type, $param, $additional);
}