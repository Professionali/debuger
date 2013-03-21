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
 * Интерфейс обработчиков ошибок
 */
interface Debug_Error_Interface {

	/**
	 * Конструктор
	 *
	 * @param Debug_View_Interface $view
	 */
	public function __construct(Debug_Processor $processor, Debug_View_Interface $view);

	/**
	 * Подходил ли данный обработчик под отображение ошибки
	 *
	 * @param integer         $code
	 * @param string|Exeption $message
	 *
	 * @return boolean
	 */
	public function is($code, $message);

	/**
	 * Отобразить переменную
	 *
	 * @param integer         $code
	 * @param string|Exeption $message
	 * @param string          $file
	 * @param integer         $line
	 * @param array           $trace
	 * @param integer         $counter
	 *
	 * @return string
	 */
	public function show($code, $message, $file=null, $line=null, $trace=array(), $counter=0);

}