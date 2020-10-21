<?php

use Lampa\Route as Route;

Route::set('default', '(<controller>(/<action>(/<id>(/<opt>))))')
->defaults([
	'controller' => 'Main',
	'directory' => 'General',
	'action'     => 'index',
]);