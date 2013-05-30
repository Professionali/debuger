<?php
/**
 * Обработчик ошибок и вывод дампа переменных
 *
 * @author    Valetin Gernovich <gernovich@ya.ru>
 * @copyright Copyright (c) 2011, Valetin Gernovich. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   0.2
 */

//if(!isset($_SERVER["SCRIPT_NAME"]) || strpos($_SERVER["SCRIPT_NAME"], 'main.php')!==false) {

	error_reporting(E_ALL);

	include_once 'Debug/Processor.php';

	Debug_Processor::getInstance(
		array(
			'errors'  => array('Database', 'Error'/*, 'Silent'*/),
			'plugins' => array('Array', 'Object', 'Image'),
			'view'    => isset($_SERVER['SERVER_PROTOCOL']) ? 'Html' : 'Cli',
			'trace_exclude' => array(
				array('class'=>'Cms_Profiler_Adapter_Page', 'function'=>'errorHandler'),
			)
		)
	);

	/**
	 * Функция вывода отладочной информации
	 *
	 * @param mixed   $arg    Иследуемая переменная
	 * @param boolean $return Возврат результата
	 * @param boolean $stop   Прекращение исполнения
	 */
	function p($arg=null, $return=false, $stop=false) {
		$debugger = Debug_Processor::getInstance();
		if($return) {
			return $debugger->dump($arg);
		} else {
			if ($stop) {
				die ($debugger->dump($arg));
			} else {
				print $debugger->dump($arg);
			}
		}
		return '';
	}
//}
