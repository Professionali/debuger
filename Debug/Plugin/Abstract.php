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
abstract class Debug_Plugin_Abstract {

	/**
	 * Представление
	 *
	 * @var Debug_View_Interface
	 */
	protected $view;

	/**
	 * Представление
	 *
	 * @var Debug_Processor
	 */
	protected $processor;

	/**
	 * Конструктор
	 *
	 * @param Debug_Processor      $processor
	 * @param Debug_View_Interface $view
	 */
	public function __construct(Debug_Processor $processor, Debug_View_Interface $view) {
		$this->processor = $processor;
		$this->view = $view;
	}
}