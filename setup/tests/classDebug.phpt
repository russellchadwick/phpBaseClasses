--TEST--
classDebug Tests
--SKIPIF--
--GET--
--POST--
--FILE--
$DEBUG = 1;
$override_config['CLI_ERROR_HANDLERS'] = array ('Display');
$override_config['DB'][1]['TYPE'] = '';
include 'begin.php';

$debug->changeDebugLevel (2);
$backtrace = $debug->getBacktrace (false);
debug ('Test Debug Level 2', 2);
$array = array ('Foo'=>array ('Subfoo', 'Subbar'), 'Bar'=>array ('Subfoo'=>'Subbar'));
debugarray ('Test Debug Array Level 1', $array, 1, __FILE__, __LINE__);
debug ('Test Debug Level 4', 4, __FILE__, __LINE__);
$debug->changeDebugLevel (4);
debug ('Test Debug Level 4', 4, __FILE__, __LINE__);
--EXPECT--
WARNING -- Test Debug Level 2
Debug Array (/usr/home/toolshed/phpBaseClasses/setup/test.php(58) : eval()'d code:10) -- Test Debug Array Level 1
&nbsp;&nbsp;&nbsp;Foo => Array
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0 => Subfoo
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1 => Subbar
&nbsp;&nbsp;&nbsp;Bar => Array
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Subfoo => Subbar
End Debug Array -- Test Debug Array Level 1
ALL (/usr/home/toolshed/phpBaseClasses/setup/test.php(58) : eval()'d code:13) -- Test Debug Level 4
