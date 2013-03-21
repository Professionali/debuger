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
 * Основной обработчик ошибок
 */
class Debug_Error_Error extends Debug_Error_Abstract implements Debug_Error_Interface {

	/**
	 * Подходил ли данный обработчик под отображение ошибки
	 *
	 * @param integer         $code
	 * @param string|Exeption $message
	 *
	 * @return boolean
	 */
	public function is($code, $message) {
		if (error_reporting() != 0) {
			return true;
		}
		return false;
	}

	/**
	 * Отобразить ошибку
	 *
	 * @param integer         $code
	 * @param string|Exeption $err
	 * @param string          $file
	 * @param integer         $line
	 * @param array           $trace
	 * @param integer         $counter
	 *
	 * @return string
	 */
	public function show($code, $err, $file=null, $line=null, $trace=array(), $counter=0) {

		if ($err instanceof Exception) {
			$message = $err->getMessage();
			$show_code = get_class($err);
		} else {
			$message = $err;
			$show_code = $code;
		}

		$id=null;
		$counter = $this->view->getOpenCloseBox($counter, $id);

		$render = $this->view->getOpenBox(
			$counter.') '.$this->view->render('bold', Debug_Processor::$error_type[$code]).': '.$message.' '.$this->view->formatLink($file, $line, "{$file} : {$line}"), $id, true
		);
		$render .= $this->view->getCloseBox(
			$counter.') '.$this->view->render('bold', Debug_Processor::$error_type[$code]).' ['.$show_code.']: '.Debug_Processor::$error_description[$code].
			$this->view->newLine($this->view->render('bold', $message)).
			$this->view->newLine($this->view->formatLink($file, $line, "{$file} : {$line}")).
			$this->processor->formatTrace($trace), $id, false
		);

		return $this->view->showError($render, $code);
	}

}