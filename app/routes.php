<?php

use Lampa\Route as Route;

Route::set('scriptExample', 'script_example')->runFile(APP . 'scripts/example.php');

Route::set('default', '(<controller>(/<action>(/<id>(/<opt>))))')
->defaults([
	'controller' => 'Main',
	'directory' => 'General',
	'action'     => 'index',
]);
