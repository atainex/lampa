<?php

define('DOCROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSTEM', DOCROOT . 'system' . DIRECTORY_SEPARATOR);
define('APP', DOCROOT . 'app' . DIRECTORY_SEPARATOR);
$domain = $_SERVER['HTTP_HOST'];
if ((!empty($_SERVER['HTTPS']) AND filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) OR (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) AND $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
	define('PROTOCOL', 'https');
} else {
	define('PROTOCOL', 'http');
}
$siteUrl = PROTOCOL.'://'.$_SERVER['HTTP_HOST'];
$siteUri = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__));
define('DOMAIN', $domain);
define('SITE_URL', $siteUrl);
define('SITE_URI', $siteUri);

if (is_file(DOCROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
	$autoload = require DOCROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
	$autoloadType = 'composer';
} else {
    $autoload = require DOCROOT . 'system' . DIRECTORY_SEPARATOR . 'autoload.php';
	$autoloadType = 'native';
}
$core = new Lampa\Core();
$core->autoload = $autoload;
$core->autoloadType = $autoloadType;
$core->config = Lampa\Config::factory(array('app'));
$core->autoload->addPsr4('App\\', array(APP . 'classes'));
require APP . 'bootstrap.php';
$core->run();