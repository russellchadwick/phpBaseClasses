--TEST--
classMath Tests
--SKIPIF--
--GET--
--POST--
--FILE--
$DEBUG = 1;
$override_config['CLI_ERROR_HANDLERS'] = array ('Display');
$override_config['DB'][1]['TYPE'] = '';
include 'begin.php';

$numbers = array (9, 18, 81, 450);
echo "Test numbers: \n";
print_r ($numbers);
$min = $math->min ($numbers);
echo "Min: $min\n";
$max = $math->max ($numbers);
echo "Max: $max\n";
$gcf = $math->gcf ($numbers);
echo "GCF: $gcf\n";
$reduce = $math->reduce ($numbers);
echo "Reduced: \n";
print_r ($reduce);
$places = $math->getDecimalPlaces (356.2398);
echo "Decimal Places of 356.2398: $places\n";
$ratio = $math->decimalToRatio (0.75);
echo "Ratio of 0.75: $ratio\n";
$decimal = $math->ratioToDecimal ('2:3', 2);
echo "Decimal of 2:3 to precision of 2: $decimal\n";
--EXPECT--
Test numbers: 
Array
(
    [0] => 9
    [1] => 18
    [2] => 81
    [3] => 450
)
Min: 9
Max: 450
GCF: 9
Reduced: 
Array
(
    [0] => 1
    [1] => 2
    [2] => 9
    [3] => 50
)
Decimal Places of 356.2398: 4
Ratio of 0.75: 3:4
Decimal of 2:3 to precision of 2: 0.67
