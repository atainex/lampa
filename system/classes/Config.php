<?php

namespace Lampa;

class Config extends \stdClass
{
	private $__isSingle;
	private $__cName;

	public static function factory($c)
	{
		$newConfig = new Config();
		if (is_array($c)) {
			$cNames = array();
			foreach ($c as $v) {
				$path = Core::fixPath(APP.'config/'.$v.'.php');
				if (file_exists($path)) {
					$arr = require($path);
					$newConfig->$v = $arr;
					$cNames[] = $v;
				}
			}
			$newConfig->__isSingle = false;
			$newConfig->__cName = $cNames;
		} else {
			$path = Core::fixPath(APP.'config/'.$c.'.php');
			if (file_exists($path)) {
				$arr = require($path);
				$newConfig->$c = $arr;
			}
			$newConfig->__isSingle = true;
			$newConfig->__cName = $c;
		}
		return $newConfig;
	}
	
	public function as_array()
	{
		$cName = $this->__cName;
		if ($this->__isSingle) {
			return $this->$cName;
		} else {
			$arr = array();
			foreach ($this->__cName as $cName) {
				$arr[$cName] = $this->$cName;
			}
			return $arr;
		}
	}
}