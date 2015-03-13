<?php
use Phalcon\DI\FactoryDefault\CLI as CliDI, Phalcon\CLI\Console as ConsoleApp;
define ( 'VERSION', '1.0.0' );
// Using the CLI factory default services container
$di = new CliDI ();
// Define path to application directory
defined ( 'APPLICATION_PATH' ) || define ( 'APPLICATION_PATH', realpath ( __DIR__ ) );
/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new \Phalcon\Loader ();
$loader->registerDirs ( array (
		APPLICATION_PATH . '/tasks',
		APPLICATION_PATH . '/models',
		APPLICATION_PATH . '/controllers',
		APPLICATION_PATH . '/library',
		APPLICATION_PATH . '/plugins'
) );
$loader->register ();
// Load the configuration file (if any)
if (is_readable ( APPLICATION_PATH . '/config/config.ini' )) {
	$config = new \Phalcon\Config\Adapter\Ini ( APPLICATION_PATH . '/config/config.ini' );
	$di->set ( 'config', $config );
}

$di->set ( 'fileUrl', function () use($config) {
	$fileUrl = new \Phalcon\Mvc\Url ();
	$fileUrl->setBaseUri ( $config->storage->baseUri );
	$fileUrl->setStaticBaseUri ( $config->storage->baseUri );
	return $fileUrl;
} );

$di->set ( 'db', function () use($config, $di) {
	$db = new $config->database->databaseClass ( array (
			'host' => $config->database->host,
			'port' => $config->database->port,
			'dbname' => $config->database->dbname,
			'username' => $config->database->username,
			'password' => $config->database->password
	) );
	// $eventsManager = $di->getShared ( 'eventsManager' );
	// $logger = $di->getShared ( 'logger' );
	// $eventsManager->attach ( 'db', function ($event, $db) use($logger) {
	// if ($event->getType () == 'beforeQuery') {
	// $logger->log ( $db->getSQLStatement (), \Phalcon\Logger::INFO );
	// }
	// } );
	// $db->setEventsManager ( $eventsManager );
	return $db;
} );

$di->set ( 'svn', function () use($config) {
	$svn = new SVN ( $config->so_svn->username, $config->so_svn->password, $config->so_svn->path );
	return $svn;
} );

// $loader = new \Phalcon\Loader ();

// $loader->registerDirs ( array (
// __DIR__ . $config->application->modelsDir,
// __DIR__ . $config->application->controllersDir,
// __DIR__ . $config->application->libraryDir,
// __DIR__ . $config->application->pluginsDir
// ) )->register ();
// Create a console application
$console = new ConsoleApp ();
$console->setDI ( $di );
$di->set ( 'console', $console );
/**
 * Process the console arguments
 */
$arguments = array ();
foreach ( $argv as $k => $arg ) {
	if ($k == 1) {
		$arguments ['task'] = $arg;
	} elseif ($k == 2) {
		$arguments ['action'] = $arg;
	} elseif ($k >= 3) {
		$arguments ['params'] [] = $arg;
	}
}
// define global constants for the current task and action
define ( 'CURRENT_TASK', (isset ( $argv [1] ) ? $argv [1] : null) );
define ( 'CURRENT_ACTION', (isset ( $argv [2] ) ? $argv [2] : null) );
try {
	// handle incoming arguments
	$console->handle ( $arguments );
} catch ( \Phalcon\Exception $e ) {
	echo $e->getMessage ();
	exit ( 255 );
}