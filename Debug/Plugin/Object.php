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
 * Плагин отображения объектов
 */
class Debug_Plugin_Object extends Debug_Plugin_Abstract implements Debug_Plugin_Interface {

	/**
	 * Максимальный уровень вложенности
	 */
	const MAX_LEVEL = 10;

	/**
	 * Уровень при котором нужно сворачивать данные
	 */
	const COLLAPSE_LEVEL = 2;

	/**
	 * Постфикс для переопределенных AOP методов
	 */
	const AOP_POSTFIX_METHOD_NAME = '__aop_original';
	
	/**
	 * Уже показанные объекты, чтоб избежать рекурсии
	 *
	 * @var array
	 */
	private $as_shown = array();

	/**
	 * Подходил ли данный плагин под отображение переменной
	 * @see Debug_Plugin_Interface::is()
	 *
	 * @param mixed $var
	 *
	 * @return boolean
	 */
	public function is($var) {
		return is_object($var);
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
		if ($tab==0) {
			$this->as_shown = array();
		}
		if (in_array($var, $this->as_shown, true)) {
			return $this->view->render('object', 'object['.get_class($var).']', ' { recursion }');;
		}
		$this->as_shown[] = $var;
		if (self::MAX_LEVEL >= $tab+1) {
			$tab++;

			$ref_object = new ReflectionObject($var);
			$class_info = $this->getClassParam($ref_object);
			$class_extends = $this->getClassExtends($ref_object);
			$class_interfaces = $this->getClassInterfaces($ref_object);
			$class_properties = $this->getClassProperties($ref_object, $var);
			$class_properties_parent = $this->getClassPropertiesParentPrivate($ref_object, $var);
			$class_properties = array_replace($class_properties, $class_properties_parent);
			$class_method = $this->getClassMethod($ref_object);
			$class_traits = $ref_object->getTraits();

			$param = array();
			foreach($class_traits as $key=>$trait) {
				$param[] = $this->view->render('key', 'use trait '.$this->view->render('object', $key)); // TODO Подумать провизуализацию примесей
			}
			foreach($class_properties as $key=>$property) {
				$param[] = $this->view->render('key', $property['property'].' property '.$this->view->render('object', $key).' -> ').$this->processor->dump($property['value'], $tab);
			}
			foreach($class_method as $key=>$method) {
				$param[] = $this->view->render('key', $method['property'].' method '.$this->view->render('object', $key)).'('.$method['params'].')';
			}
			$view = $this->view;
			$render = $this->view->render('object', '', ($class_extends?' << '.$class_extends:'').($class_interfaces?' : '.$class_interfaces:'').' {');
			$render .= implode(', ', array_map(function($val) use ($tab, $view) {
				return $view->newLine($val, $tab);
			}, $param));
			$tab--;
			$render .= $view->newLine('}', $tab);

			$id=null;
			$additional = $view->getOpenCloseBox('object['.$class_info.' '.get_class($var).']', $id);

			if(self::COLLAPSE_LEVEL >= $tab) {
				$render = $view->getOpenBox($render, $id);
				$render .= $view->getCloseBox(' {...}', $id, true);
			} else {
				$render = $view->getCloseBox($render, $id);
				$render .= $view->getOpenBox(' {...}', $id, true);
			}
			return $this->view->render('object', $additional, $render);
		} else {
			return $this->view->render('object', 'object['.get_class($var).']', ' {...}');
		}
	}


	/**
	 * Получить данные о свойствах класса
	 *
	 * @param ReflectionObject $ref_object
	 */
	private function getClassParam(ReflectionObject $ref_object) {
		$params = array();
		$inspects = array(
			'isInternal'  => array('internal', 'user'),
			'isFinal'     => array('final'),
			'isAbstract'  => array('abstract'),
			'isInterface' => array('interface'),
			'isTrait'     => array('trait'),
		);
		foreach ($inspects as $inspect=>$strings) {
			if ($ref_object->{$inspect}()) {
				$params[] = $strings[0];
			} elseif (isset($strings[1])) {
				$params[] = $strings[1];
			}
		}
		if (!$ref_object->isInterface() && !$ref_object->isTrait()) {
			$params[] = 'class';
		}
		return implode(' ', $params);
	}

	/**
	 * Получить данные о наследуемых классах
	 *
	 * @param ReflectionObject $ref_class
	 */
	private function getClassExtends(ReflectionObject $ref_class) {
		$params=array();
		$extends = $ref_class->getParentClass();
		while (($ref_class = $ref_class->getParentClass()) instanceof ReflectionClass) {
			$params[] = $ref_class->getName();
		}
		return implode(' << ', $params);
	}

	/**
	 * Получить данные о реализуемых интерфейсах
	 *
	 * @param ReflectionObject $ref_class
	 */
	private function  getClassInterfaces(ReflectionObject $ref_class) {
		return implode(' : ', array_keys($ref_class->getInterfaces()));
	}

	/**
	 * Получить данные о свойствах
	 *
	 * @param ReflectionObject $ref_class
	 * @param unknown_type $object
	 */
	private function getClassProperties(ReflectionObject $ref_class, $object) {
		$inspects = array(
			'isPublic'    => 'public',
			'isPrivate'   => 'private',
			'isProtected' => 'protected',
			'isStatic'    => 'static',
		);
		$properties = $ref_class->getProperties();
		$ret = array();
		foreach($properties as $ref_prop) {
			$params = array();
			foreach ($inspects as $inspect=>$info) {
				if ($ref_prop->{$inspect}()) {
					$params[] = $info;
				}
			}
			$param_properties  = implode(' ', $params);
			if(!$ref_prop->isPublic()) {
				$ref_prop->setAccessible(true);
			}
			$value = $ref_prop->getValue($object);
			$ret[$ref_prop->getName()] = array('property'=>$param_properties, 'value'=>$value);
		}
		return $ret;
	}

	/**
	 * Получить данные о свойствах родительских классов
	 *
	 * @param ReflectionObject $ref_class
	 * @param unknown_type $object
	 */
	private function getClassPropertiesParentPrivate(ReflectionObject $ref_class, $object) {

		$inspects = array(
			'isPrivate'   => 'private',
			'isStatic'    => 'static',
		);

		$ret = array();

		while (($ref_class = $ref_class->getParentClass()) instanceof ReflectionClass) {

			$properties = $ref_class->getProperties();

			foreach($properties as $ref_prop) {
				$params = array();
				if ($ref_prop->isPrivate()) {
					foreach ($inspects as $inspect=>$info) {
						if ($ref_prop->{$inspect}()) {
							$params[] = $info;
						}
					}
				}
				$param_properties  = implode(' ', $params);
				$ref_prop->setAccessible(true);
				$value = $ref_prop->getValue($object);
				$ret[$ref_class->getName().'::'.$ref_prop->getName()] = array('property'=>$param_properties, 'value'=>$value);
			}
		}
		return $ret;
	}




	/**
	 * Получить данные о методах
	 *
	 * @param ReflectionObject $ref_class
	 */
	private function getClassMethod(ReflectionObject $ref_class) {
		$inspects = array(
			'isAbstract'  => 'abstract',
			'isFinal'  => 'final',
			'isPublic'  => 'public',
			'isPrivate'  => 'private',
			'isProtected'  => 'protected',
			'isStatic'  => 'static',
		);
		$metods = $ref_class->getMethods();
		
		// Удаляем из инспекции метод пореопледеленный через AOP
		// TODO помечать AOP методы
		foreach($metods as $key=>$ref_method) {
			if(substr($ref_method->getName(), strlen($ref_method->getName()) - strlen(self::AOP_POSTFIX_METHOD_NAME)) == self::AOP_POSTFIX_METHOD_NAME) {
				unset($metods[$key]);
			}
		}
		
		$ret = array();
		foreach($metods as $ref_method) {
			$params = array();
			foreach ($inspects as $inspect=>$info) {
				if ($ref_method->{$inspect}()) {
					$params[] = $info;
				}
			}
			//$params[] = $ref_method->getDeclaringClass()->getName();
			$parameters = array();
			$opion=0;
			foreach ($ref_method->getParameters() as $param) {
				if ($param->isOptional()) {
					$opion++;
				}
				$param_info = array();
				$param_info[] = $param->isOptional() ? '[' : '';
				$param_info[] = is_object($param->getClass()) ? $param->getClass()->getName().' ' : '';
				$param_info[] = $param->isArray() ? 'array ' : '';
				$param_info[] = $param->isPassedByReference() ? '&' : '';
				$param_info[] = '$'.$param->getName();
				$param_info[] = $param->isDefaultValueAvailable () ? '='.$this->processor->dump($param->getDefaultValue(),0,true) : '';
				$parameters[] = implode('', $param_info);
			}
			$parameters = implode(', ', $parameters).$this->view->render('key',  '', str_repeat(']',$opion), 'key');
			$param_method  = implode(' ', $params);
			$ret[$ref_method->getName()] = array('property'=>$param_method, 'params'=>$parameters);
		}
		return $ret;
	}
}
