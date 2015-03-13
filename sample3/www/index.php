<?php
if (! defined ( 'MICRO_TEST' )) {
	ini_set ( 'display_errors', false );
}

if (! isset ( $_GET ['_url'] ) && isset ( $_SERVER ['PATH_INFO'] ))
	$_GET ['_url'] = $_SERVER ['PATH_INFO'];
defined ( 'APP_PATH' ) || define ( 'APP_PATH', realpath ( __DIR__ ) );
define ( 'APP_NAME', 'Solidarity Server' );
$config = new Phalcon\Config\Adapter\Php ( APP_PATH . '/app/config/config.php' );
$loader = new Phalcon\Loader ();
$loader->registerDirs ( $config->app->loaderDirs->toArray () )->register ();

$di = new \Phalcon\DI\FactoryDefault();

require APP_PATH . '/app/config/services.php';
// $di->get('facebookAdapter')->bind('');
$app = new Phalcon\Mvc\Application ();
$app->setDI ( $di );
$di->setShared('app', $app);
// remove global vars
$di = null;
unset ( $di );
$config = null;
unset ( $config );
$loader = null;
unset ( $loader );
/*
 * $app->post ( '/register', function () use($app) {
 * $response = $app->response;
 * $body = $app->request->getRawBody ();
 * $body = json_decode ( $body, true );
 * if (! $body) {
 * return $app->handle ( '/404' );
 * }
 * $empty = [
 * 'package' => '',
 * 'android_id' => '',
 * 'adid' => '',
 * 'imei' => '',
 * 'apps' => [ ]
 * ];
 * $body = array_merge ( $empty, $body );
 * $ret = [
 * 'sequence' => [
 * 'admob',
 * 'mm',
 * 'inmobi'
 * ],
 * 'url' => 'http://app.gamepromote.net/redirect?to=com.tap.white.tile&from=com.blood.zombies2&type=featured&i=863175020092868&a=28ec9d3662e557e',
 * 'image' => 'http://cdn.gamepromote.net/images/eca1c45eebfc1ecbb1ed4e598fd08653.jpg',
 * 'interval' => 2,
 * 'package' => 'com.tap.white.tile',
 * 'type' => 'featured',
 * 'body' => $body
 * ];
 * $response->setJsonContent ( $ret );
 * $response->setContentType ( 'application/json', 'UTF-8' );
 * return $response;
 * } );
 * $app->post ( '/show', function () use($app) {
 * $response = $app->response;
 * return $app->response;
 * } );
 * //$app->map ( '/', function () use($app) {
 * //	$response = $app->response;
 * //	$response->setContent ( '<h1>Hello World!</h1>' );
 * //	return $response;
 * //} );
 * $app->notFound ( function () use($app) {
 * $app->response->setStatusCode ( 404, 'Not Found' )->sendHeaders ();
 * $app->response->setContent ( '<h1>404</h1>' );
 * return $app->response;
 * } );
 */
$response = $app->handle ();
$response->send ();

