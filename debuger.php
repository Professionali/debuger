<?php
/**
 * Обработчик ошибок и вывод дампа переменных
 *
 * @author    Valetin Gernovich <gernovich@ya.ru>
 * @copyright Copyright (c) 2011, Valetin Gernovich. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   0.2
 */


include_once 'Debug/Processor.php';

Debug_Processor::getInstance(array(
	'errors'  => array('Database', 'Error'/*, 'Silent'*/),
	'plugins' => array('Array', 'Object', 'Image'),
	'view'    => (PHP_SAPI != 'cli') ? 'Html' : 'Cli',
	'trace_exclude' => array(
		array('class' => 'Cms_Profiler_Adapter_Page', 'function' => 'errorHandler'),
	)
));

/**
 * Функция вывода отладочной информации
 *
 * @param mixed   $arg    Иследуемая переменная
 * @param boolean $return Возврат результата
 * @param boolean $stop   Прекращение исполнения
 */
function p($arg = null, $return = false, $stop = false) {
	$dump = Debug_Processor::getInstance()->dump($arg);
	if ($return) {
		return $dump;
	} elseif ($stop) {
		exit($dump);
	} else {
		print $dump;
	}
	return '';
}
