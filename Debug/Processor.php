<?php
/**
 * Обработчик ошибок и вывод дампа переменных
 *
 * @author    Valetin Gernovich <gernovich@ya.ru>
 * @copyright Copyright (c) 2011, Valetin Gernovich. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   0.2
 */

include_once 'Error/Interface.php';
include_once 'Error/Abstract.php';
include_once 'Error/Database.php';
include_once 'Error/Error.php';
include_once 'Error/Silent.php';

include_once 'View/Interface.php';
include_once 'View/Abstract.php';
include_once 'View/Cli.php';
include_once 'View/Html.php';

include_once 'Plugin/Interface.php';
include_once 'Plugin/Abstract.php';
include_once 'Plugin/Base.php';
include_once 'Plugin/Array.php';
include_once 'Plugin/Object.php';
include_once 'Plugin/Image.php';

/**
 * Процессор ошибок
 */
class Debug_Processor {

	const E_SILENT = '@';
	const E_EXCEPTION = 'e';

	const SILENT = 'Silent';
	const EXCEPTION = 'Exception';
	const ERROR = 'Error';
	const WARNING = 'Warning';
	const NOTICE = 'Notice';
	const DEPRECATED = 'Deprecated';

	/**
	 * Типы ошибок
	 *
	 * @var array
	 */
	public static $error_type = array (
			self::E_SILENT      => self::SILENT,
			self::E_EXCEPTION   => self::EXCEPTION,
			E_ERROR             => self::ERROR,
			E_WARNING           => self::WARNING,
			E_PARSE             => self::ERROR,
			E_NOTICE            => self::NOTICE,
			E_CORE_ERROR        => self::ERROR,
			E_CORE_WARNING      => self::WARNING,
			E_COMPILE_ERROR     => self::ERROR,
			E_COMPILE_WARNING   => self::WARNING,
			E_USER_ERROR        => self::ERROR,
			E_USER_WARNING      => self::WARNING,
			E_USER_NOTICE       => self::NOTICE,
			E_STRICT            => self::NOTICE,
			E_RECOVERABLE_ERROR => self::ERROR,
			E_DEPRECATED        => self::DEPRECATED,
			E_USER_DEPRECATED   => self::DEPRECATED,
	);

	/**
	 * Сообщения об ошибках
	 *
	 * @var array
	 */
	public static $error_description = array (

			self::E_SILENT      => 'Silent error',
			self::E_EXCEPTION   => 'Uncaught exception',
			E_ERROR             => 'Fatal runtime Error',
			E_WARNING           => 'Warning during runtime. Not a fatal error',
			E_PARSE             => 'Parsing error at compile time',
			E_NOTICE            => 'Notice during a less serious than a warning',
			E_CORE_ERROR        => 'Fatal error in the initial startup PHP',
			E_CORE_WARNING      => 'Prevention is not a fatal error in the initial startup files',
			E_COMPILE_ERROR     => 'Fatal Error during compilation',
			E_COMPILE_WARNING   => 'Warning compile time, not a fatal error',
			E_USER_ERROR        => 'User create Error',
			E_USER_WARNING      => 'User create Warning',
			E_USER_NOTICE       => 'User create Notification',
			E_STRICT            => 'Run-time notices',
			E_RECOVERABLE_ERROR => 'Catchable fatal error',
			E_DEPRECATED        => 'Warning. Code that will not work in future versions',
			E_USER_DEPRECATED   => 'User create Warning. Code that will not work in future versions',

			/*
			self::E_SILENT      => 'Подавленная ошибка',
			self::E_EXCEPTION   => 'Необработанное исключение',
			E_ERROR             => 'Неустранимая ошибка выполнения',
			E_WARNING           => 'Предупреждение в ходе выполнения. Не фатальная ошибка',
			E_PARSE             => 'Разбор ошибок во время компиляции',
			E_NOTICE            => 'Уведомление в ходе выполнения. Не фатальная ошибка',
			E_CORE_ERROR        => 'Неустранимая ошибка в первоначальном запуске PHP',
			E_CORE_WARNING      => 'Предупреждение - это не фатальная ошибка в первоначальной загрузки файлов',
			E_COMPILE_ERROR     => 'Критическая ошибка во время компиляции',
			E_COMPILE_WARNING   => 'Предупреждение время компиляции, не фатальная ошибка',
			E_USER_ERROR        => 'Ошибка созданная пользователем',
			E_USER_WARNING      => 'Предупреждение созданное пользователем',
			E_USER_NOTICE       => 'Уведомление созданное пользователем',
			E_STRICT            => 'При выполнении произошло уведомление',
			E_RECOVERABLE_ERROR => 'Кэшированная фатальная ошибка',
			E_DEPRECATED        => 'Предупреждение. Не поддерживается в будущих версиях',
			E_USER_DEPRECATED   => 'Предупреждение созданное пользователем. Не поддерживается в будущих версиях',
			*/
	);

	/**
	 * Инстантс обработчика
	 *
	 * @var Debug_Processor
	 */
	private static $instance;

	/**
	 * Представление
	 *
	 * @var Debug_View_Interface
	 */
	private $view;

	/**
	 * Плагины вывода ошибок
	 *
	 * @var array
	 */
	private $errors=array();

	/**
	 * Плагины вывода дампа
	 *
	 * @var array
	 */
	private $plugins=array();

	/**
	 * Счетчик ошибок
	 *
	 * @var integer
	 */
	private $counter=0;

	/**
	 * Шаблон для ссылок вида sheme://path/%s:%d
	 *
	 * @var string
	 */
	private $link_template = null;

	/**
	 * Путь к проекту
	 *
	 * @var string
	 */
	private $base_pach = null;

	/**
	 * Исключать из трэйса все выше указанных данных: function и/или class
	 * 
	 * @var array
	 */
	private $trace_exclude = array();

	/**
	 * Конструктор
	 *
	 * @param array $config
	 */
	private function __construct(array $config=array()) {

		ini_set('display_errors', '0');
		ini_set('log_errors', '1');
		ini_set('display_startup_errors', '1');
		ini_set('html_errors', '0');

		$class = 'Debug_View_'.$config['view'];
		$this->view = new $class($this);
		$config['plugins'][] = 'Base';
		if (isset($config['plugins'])) {
			foreach ($config['plugins'] as $plugin_name) {
				$class = 'Debug_Plugin_'.$plugin_name;
				$this->plugins[$plugin_name] = new $class($this, $this->view);
			}
		}
		if (isset($config['errors'])) {
			foreach ($config['errors'] as $error_name) {
				$class = 'Debug_Error_'.$error_name;
				$this->errors[$error_name] = new $class($this, $this->view);
			}
		}
		if (isset($config['trace_exclude'])) {
			$this->trace_exclude = $config['trace_exclude'];
		}
		$this->registerHandlers();
	}

	/**
	 * Зарегистрировать обработчики ошибок
	 */
	private function registerHandlers () {
		// Устанавливаем собственный обработчик ошибок
		set_error_handler(array($this,'errorHandler'));
		// Устанавливаем собственный обработчик исключений
		set_exception_handler(array($this,'exceptionHandler'));
		// Обработка фатальных ошибок
		register_shutdown_function(array($this, 'fatalErrorHandler'));
	}

	/**
	 * Обработка ошибок
	 *
	 * @param integer         $code
	 * @param string|Exeption $message
	 * @param string          $file
	 * @param integer         $line
	 * @param array           $context
	 * @param array           $trace
	 * @param boolean         $return
	 */
	public function errorHandler($code, $message, $file=null, $line=null, array $context=array(), array $trace=array(), $return=false) {

		$this->counter++;

		if(empty($trace)) {
			$trace = debug_backtrace();
			foreach ($trace as $key=>$stack) {
				if (
					(isset($stack['class']) && strpos($stack['class'], 'Debug_')===0) ||
					$stack['function'] == 'trigger_error'
				) {
					unset($trace[$key]);
				}
			}
		}

		foreach ($this->errors as $mame=>$error) {
			if (call_user_func(array($error, 'is'), $code, $message)) {
				$ret = call_user_func(array($error, 'show'), $code, $message, $file, $line, $trace, $this->counter);
				if ($return) {
					return $ret;
				} else {
					print $ret;
				}
			}
		}
	}

	/**
	 * Обработка неперехваченных исключений
	 *
	 * @param Exception $e
	 */
	public function exceptionHandler(Exception $e, $return=false) {
		$ret = $this->errorHandler(self::E_EXCEPTION, $e, $e->getFile(), $e->getLine(), array(), $e->getTrace(), true);
		if ($return) {
			return $ret;
		} else {
			print $ret;
			exit;
		}
	}

	/**
	 * Обработчик фатальных ошибок
	 */
	public function fatalErrorHandler() {
		$error = error_get_last();
		if ($error && in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE), true)) {
			print $this->errorHandler($error['type'], $error['message'], $error['file'], $error['line'], array(), array(), true);
		}
	}

	/**
	 * Установить шаблон ссылок и базовый путь
	 */
	public function getLinkTemplate() {
		return $this->link_template;
	}

	/**
	 * Установить шаблон ссылок и базовый путь
	 */
	public function getBasePach() {
		return $this->base_pach;
	}

	/**
	 * Установить шаблон ссылок и базовый путь
	 *
	 * @param string $template
	 * @param string $base
	 *
	 * @return Debug_Processor
	 */
	public function setLinkTemplete($template, $base) {
		$this->link_template = $template;
		$this->base_pach = $base;
		return $this;
	}

	/**
	 * Вывод форматированного трэйса
	 *
	 * @param array $trace
	 * @return string
	 */
	public function formatTrace($trace) {

		$key_index = null;

		$current_key_index = 0;
		foreach($trace as $data) {
			$current_key_index++;
			foreach($this->trace_exclude as $exclude) {
				if(isset($data['class']) && isset($data['function']) && $exclude['class'] == $data['class'] && $exclude['function'] == $data['function']) {
					$key_index = $current_key_index;
				}
			}
		}

		if (!is_null($key_index)) {
			$trace = array_slice($trace, $key_index);
		}

		$ret = '';
		foreach ($trace as $stack) {

			$object = '';
			$class = '';
			$type = '';

			if($stack['function']=='{closure}') {
				$stack['function'] = '__invoke';
				$stack['class'] = 'Closure';
				$stack['type'] = '->';
			}
			if (isset($stack['object'])) {
				$object = 'Object ';
			}
			if (isset($stack['class'])) {
				$class = $stack['class'].'';
			}
			if (isset($stack['type'])) {
				$type = $stack['type'];
			}

			$args = array();
			foreach (isset($stack['args'])?$stack['args']:array() as $arg) {
				$args[] = $this->dump($arg,0,true);
			}

			$file = isset($stack['file']) ? $stack['file'] : 'unknown';
			$line = isset($stack['line']) ? $stack['line'] : 'unknown';

			$ret .= $this->view->newLine(
				$this->view->newLine($object.$class.$type.''.$stack['function'].'('.implode(', ', $args).');', 1).
				$this->view->newLine($this->view->formatLink($file, $line, "{$file} : {$line}"), 1)
			);

		}
		return $ret;
	}

	/**
	 * Вывод дампа
	 *
	 * @param mixed $arg
	 * @param integer $tab
	 * @param boolean $base
	 */
	public function dump($arg, $tab=0, $base=false) {
		if ($base) {
			return call_user_func(array($this->plugins['Base'],'format'), $arg, $tab);
		}
		foreach ($this->plugins as $plugin) {
			if ($plugin->is($arg)) {
				if ($tab!=0) {
					return $plugin->format($arg, $tab);
				} else {
					return $this->view->showDump($plugin->format($arg, $tab));
				}
			}
		}
	}

	public static function getInstance(array $config=array()) {
		if (!isset(self::$instance)) {
			self::$instance = new self($config);
		}
		return self::$instance;
	}

	public function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	public function __wakeup(){
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
	}
}
