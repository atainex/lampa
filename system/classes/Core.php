<?php

namespace Lampa;

class Core
{
	public static $init;
    public $autoloader;
	public $request;
	public $controller;
	public $directory;

	function __construct() {
		$this->request = new Request();
	}

	public static function fixPath($path)
	{
		return str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', $path));
	}
	
	public function run()
	{
		if ($this->request->process()) {
			// Запуск соответствующего контроллера и акта
			$this->execute();
		} else {
			// Тут 404
			die('404: нет правила');
		}
	}
	
	public function execute() {
		Core::$init = $this;
		if (!empty($this->request->route->getFileEte())) {
            if (file_exists($this->request->route->getFileEte())) {
				require $this->request->route->getFileEte();
				exit();
			} else {
				if (file_exists($this->request->route->getIfNotFoundFileEte())) {
					require $this->request->route->getIfNotFoundFileEte();
					exit();
				} else {
					die('404: не найден искомый исполняемый файл');
				}
			}
		}
		$controllerName = 'App\\Controllers\\'.((!empty($this->request->directory))?$this->request->directory.'\\':'').$this->request->controller;
		$actionName = $this->request->action;
		if (!class_exists($controllerName)) {
			die('404: не найден искомый controller');
		}
		Core::$init->controller = new $controllerName();
		if (!method_exists(Core::$init->controller, $actionName)) {
			die('404: не найден искомый action');
		}
		Core::$init->controller->before();
		Core::$init->controller->$actionName();
		Core::$init->controller->after();
	}
}