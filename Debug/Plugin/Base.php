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
 * Базовый плагин отображения переменных
 * Используется в основном для отображения данных в параметрах функций
 */
class Debug_Plugin_Base extends Debug_Plugin_Abstract implements Debug_Plugin_Interface {

    /**
     * Максимальный уровень вложенности
     */
    const MAX_LEVEL = 1;

    /**
     * Подходил ли данный плагин под отображение переменной
     * @see Debug_Plugin_Interface::is()
     *
     * @param mixed $var
     *
     * @return boolean
     */
    public function is($var) {
        return true;
    }

    /**
     * Отобразить переменную
     * TODO добавить обработку рекурсии
     * @see Debug_Plugin_Interface::format()
     *
     * @param mixed $var
     * @param integer $tab
     *
     * @return string
     */
    public function format($var, $tab=0) {

        $param = '';
        $type = 'unknown';
        $additional = 'unknown';

        if (!is_array($var) || (is_array($var) && self::MAX_LEVEL >= $tab+1)) {
            switch (true) {
                case is_bool($var):
                    $param  = $var ? ' true' : ' false';
                    $type = 'boolean';
                    $additional = 'boolean';
                    break;

                case is_int($var):
                    $param  = ' '.strval($var);
                    $type = 'integer';
                    $additional = 'integer';
                    break;

                case is_float($var):
                    $param  = ' '.strval($var);
                    $type = 'float';
                    $additional = 'float';
                    break;

                case is_string($var):

                    $type = 'string';
                    $len = strlen($var);
                    $additional = 'string['.$len.']';
					
                    if($len > 256) {
                        $var = mb_substr($var, 0, 256).'...';
                    }
                    $param = ' "'.$this->view->escape($var).'"';
                    break;

                case is_array($var):
                    $param = array();
                    $tab++;
                    foreach($var as $key=>$value) {
                        $param[] = $this->view->render('key', (is_int($key)?"{$key}":"'{$key}'")."=> ").$this->format($value, $tab);
                    }
                    $tab--;
                    $param = '('.implode(', ', $param).')';
                    $type = 'array';
                    $additional = 'array['.count($var).']';
                    break;

                case is_object($var):
                    $param = ' '.get_class($var);
                    $type = 'object';
                    $additional = 'object';
                    break;

                case is_resource($var):
                    $param = ' '.get_resource_type($var);
                    $type = 'resource';
                    $additional = 'resource';
                    break;

                case is_null($var):
                    $param = '';
                    $type = 'null';
                    $additional = 'null';
                    break;
            }
            return $this->view->render($type, $additional, $param);
        } else {
            return $this->view->render('array', 'array['.count($var).']', '(...)');
        }
    }
}
