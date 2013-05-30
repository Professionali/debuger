<?php
/**
 * Обработчик ошибок и вывод дампа переменных
 *
 * @author    Valetin Gernovich <gernovich@ya.ru>
 * @copyright Copyright (c) 2011, Valetin Gernovich. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   0.2
 */


include 'debugger.php';


interface bar {}
interface bar2 {}

class foo2 {}
class foo3 extends foo2 {}

final class foo extends foo3 implements bar, bar2 {
	private $a = null;
	protected $b = 0;
	public $c = '';

	public function __construct() {}
	public function test($a, $b, array $c = array()) {
		return self::bar($c);
	}
	private static function bar($e){
		return aaaa($e);
	}
}

function aaaa() {
	zxcvzcxv;
	@zxcvzcxv;
}


throw new Exception('Генерим исключение');

// Рекурсия в массивах
//$array = array('foo'=>1, 'bar'=>2);
//$array['test'] = &$array;
//p($array);


// Рекурсия в объектах
//p(Debug_Processor::getInstance());

$obj = new foo();
$obj->test('Привет', 2, array(null));

p(array(
	'boolean'  => true,
	'integer'  => 999,
	'float'    => pi(),
	'string'   => 'test',
	'array'    => array(0,null=>null,'d'=>1, 'tag'=>"<b>test</b>", 'tag'=>"<b>test</b>", 'cli'=>"\x1b[41m\n\t\r"),
	'object'   => new foo(),
	'resource' => imagecreatetruecolor(14 , 14),
	'null'     => null,
	'ar'       => array(0,1,2,'asda','',null,pi(),false,true,array(array(array(array(array(array(array(array(array()))))))))),
));


print "\n";
