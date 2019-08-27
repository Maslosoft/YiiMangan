<?php

use Maslosoft\Addendum\Addendum;
use Maslosoft\Mangan\Annotations\MetaOptionsHelper;
use Maslosoft\Mangan\Annotations\Validators\ValidatorAnnotation;
use Maslosoft\Mangan\Mangan;
use Maslosoft\Mangan\Validators\Proxy\RequiredProxy;
use Maslosoft\ManganTest\Models\ValidatorProxy\RequiredValidator;
use Maslosoft\Mangan\Command;
use Maslosoft\Mangan\Tools\AvailableCommandsGenerator;
use Maslosoft\Mangan\Transaction;
use Maslosoft\ManganTest\Models\BaseAttributesAnnotations;

date_default_timezone_set('Europe/Paris');

define('VENDOR_DIR', __DIR__ . '/../vendor');
define('YII_DIR', VENDOR_DIR . '/yiisoft/yii/framework/');
require VENDOR_DIR . '/autoload.php';

if (!defined('MANGAN_TEST_ENV'))
{
	define('MANGAN_TEST_ENV', true);
}

$config = require __DIR__ . '/config.php';

$addendum = new Addendum();
$addendum->namespaces[] = MetaOptionsHelper::Ns;
$addendum->namespaces[] = ValidatorAnnotation::Ns;
$addendum->init();

const ManganFirstDbName = 'ManganTest';
const ManganSecondDbName = 'ManganTestSecond';
const ManganThirdDbName = 'ManganTestThird';
const ManganForthDbName = 'ManganTestFour';
const ManganCustomValidatorsDbName = 'ManganTestCustomValidators';

$mangan = new Mangan();
$mangan->connectionString = 'mongodb://localhost:27017';
$mangan->dbName = ManganFirstDbName;
$mangan->init();

$mangan2 = new Mangan('second');
$mangan2->connectionString = 'mongodb://localhost:27017';
$mangan2->dbName = ManganSecondDbName;
$mangan2->init();

$mangan3 = new Mangan('tokumx');
$mangan3->connectionString = 'mongodb://localhost:27017';
$mangan3->dbName = ManganThirdDbName;
$mangan3->init();

$mangan3 = new Mangan('four');
$mangan3->connectionString = 'mongodb://localhost:27017';
$mangan3->dbName = ManganForthDbName;
$mangan3->init();


$mangan4 = new Mangan('custom-validators');
$mangan4->connectionString = 'mongodb://localhost:27017';
$mangan4->dbName = ManganCustomValidatorsDbName;
$mangan4->validators[RequiredProxy::class] = RequiredValidator::class;
$mangan4->init();

// Here you can initialize variables that will be available to your tests
error_reporting(E_ALL);

echo "Mangan: " . (new Mangan())->getVersion() . PHP_EOL;
echo "MongoDB: " . (new Command())->buildInfo()['version'] . PHP_EOL;
$transactions = 'false';
$t = (new Transaction(new BaseAttributesAnnotations));
$t->commit();
if ($t->isAvailable())
{
	$transactions = 'true';
}
(new AvailableCommandsGenerator)->generate();
echo "Transactions: " . $transactions . PHP_EOL;

foreach(['mongodb', 'second', 'tokumx', 'custom-validators'] as $connectionId)
{
	echo "Using DB: " . Mangan::fly($connectionId)->dbName . PHP_EOL;
}
ini_set('xdebug.max_nesting_level', 200);
