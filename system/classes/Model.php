<?php

namespace Lampa;

class Model
{
	public static function factory($name)
	{
		$mName = 'App\\Models\\' . $name;
		return new $mName();
	}
}