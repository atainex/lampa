<?php

namespace Lampa;

class LampaLoader
{
	function __construct() {
		/*
		* Здесь можно перечислить те пространства имён и директорию к классам этого пространства, 
		* которые должны грузиться в первую очередь (например системные)
		*/
		$this->addPsr4('Lampa\\', array(SYSTEM.'classes'));
	}
	protected $namespaceMap = array();
	protected $prefixMap = array();

	public function addPsr4($namespace, $path, $prepend = false)
	{
		$length = strlen($namespace);
		if ('\\' !== $namespace[$length - 1]) {
			die('Не пустой PSR-4 префикс должен быть со слешем (\) в конце');
		}
		// $fixPath = static::fixPath(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $path[0]);
		$fixPath = static::fixPath($path[0]);
		if (is_dir($fixPath)){
			$this->namespaceMap[$namespace] = $fixPath;
			$this->prefixMap[$namespace[0]][$namespace] = $length;
			$this->register();
			return true;
		}
		return false;
	}

	public function register()
	{
		spl_autoload_register(array($this, 'autoload'));
	}

	protected function autoload($class)
	{
		if (!empty($this->prefixMap[$class[0]])) {
			$sortPrefixes = uksort($this->prefixMap[$class[0]], function ($a, $b) {
				return strlen($b) - strlen($a);
			});
			foreach ($this->prefixMap[$class[0]] as $kPref => $vPref) {
				if(stristr($class, $kPref)) {
					$sPref = $kPref;
					break;
				}
			}
			$pathParts = explode('\\', str_replace($sPref, '', $class));
			if (is_array($pathParts)){
				if (!empty($this->namespaceMap[$sPref])) {
					$filePath = $this->namespaceMap[$sPref]. DIRECTORY_SEPARATOR .implode(DIRECTORY_SEPARATOR, $pathParts).'.php';
					if (file_exists($filePath)) {
						require_once $filePath;
						return true;
					} else {
						return false;
					}
				}
			}
		} else {
			die('Класс «'.$class.'» не найден в системе');
		}
		return false;
	}
	
	public static function fixPath($path)
	{
		return str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', $path));
	}

}

return new LampaLoader();