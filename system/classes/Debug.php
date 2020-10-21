<?php

namespace Lampa;

class Debug
{
	public $factory = null;
	
	public static function dump($array, $preset = null)
	{
		echo '<pre>';
		if (!empty($preset)) {
			echo $preset . '<br>';
		}
		print_r($array);
		echo '</pre>';
		die();
	}
}