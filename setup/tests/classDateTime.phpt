--TEST--
classDateTime Tests
--SKIPIF--
--GET--
--POST--
--FILE--
$DEBUG = 1;
$override_config['CLI_ERROR_HANDLERS'] = array ('Display');
$override_config['DB'][1]['TYPE'] = '';
include 'begin.php';

$timestamp = $datetime->mktime (14, 30, 50, 11, 05, 2001);
echo "Timestamp of 2001-11-05 14:30:50: $timestamp\n";
$date = $datetime->date ('a A D d F g G h H i j L l M m n S s t U W w X x Y y z', $timestamp);
echo "Date Info for that Timestamp: $date\n";
$timestamp = $datetime->mktime (2, 11, 28, 02, 29, 2000);
echo "Timestamp of 2000-02-29 02:11:28: $timestamp\n";
$getdate = $datetime->getdate ($timestamp);
echo "Getdate Info for that Timestamp: \n";
print_r ($getdate);
$daysbetween = $datetime->daysBetween ('2002-03-27', '02/29/2003');
echo "Days Between 2002-03-27 and 02/29/2003: $daysbetween\n";
$daysbetween = $datetime->daysBetween (58314650, '02/29/2003');
echo "Days Between 58314650 and 02/29/2003: $daysbetween\n";
$daysbetween = $datetime->daysBetween ('2002-03-27', '02/29/2003', true);
echo "Business Days Between 2002-03-27 and 02/29/2003: $daysbetween\n";
$adddays = $datetime->addDays ('2000-02-15', 335);
echo "Add 335 days to 2000-02-15: $adddays\n";
$adddays = $datetime->addDays ('2000-02-15', 335, true);
echo "Add 335 business days to 2000-02-15: $adddays\n";
--EXPECT--
Timestamp of 2001-11-05 14:30:50: 58314650
Date Info for that Timestamp: pm PM Mon 05 November 2 14 02 14 30 5 0 Monday Nov 11 11 th 50 30 58285850 44 1 1 4 2001 01 308
Timestamp of 2000-02-29 02:11:28: 5134288
Getdate Info for that Timestamp: 
Array
(
    [seconds] => 28
    [minutes] => 11
    [hours] => 02
    [mday] => 29
    [wday] => 2
    [mon] => 02
    [year] => 2000
    [yday] => 59
    [weekday] => Tuesday
    [month] => February
    [0] => 5134288
)
Days Between 2002-03-27 and 02/29/2003: 339
Days Between 58314650 and 02/29/2003: 481
Business Days Between 2002-03-27 and 02/29/2003: 235
Add 335 days to 2000-02-15: 2001-01-15
Add 335 business days to 2000-02-15: 2001-06-08
