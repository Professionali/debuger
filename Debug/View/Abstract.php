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
 * Абстрактный плагин
 */
abstract class Debug_View_Abstract {

	/**
	 * Представление
	 *
	 * @var Debug_Processor
	 */
	protected $processor;

	/**
	 * Конструктор
	 *
	 * @param Debug_Processor $processor
	 */
	public function __construct(Debug_Processor $processor) {
		$this->processor = $processor;
	}
}