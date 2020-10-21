<?php

namespace Lampa;

class Request
{
    public $controller;
    public $action;
    public $params;
    public $route;
	private $_uri;
	private $init = null;
	
	/* Добавить память метода запроса в file_get_contents('php://input'); */

	function __construct() {
		$this->uri($_SERVER['REQUEST_URI']);
	}
	public static function factory() {
		Request::$init = new Request();
	}
	
	public function redirect($url, $permanent = false) {
		header('Location: ' . $url, true, $permanent ? 301 : 302);
		exit();
	}
	
	public function param($name, $default = null) {
		return Arr::get($this->params, $name, $default);
	}
	
	public function uri($uri = NULL) {
		if ($uri === NULL) {
			return ($this->_uri === '') ? '/' : $this->_uri;
		}
		$this->_uri = $uri;
		return $this;
	}
	
	public static function process($request) {
		$routes = (empty($routes)) ? Route::all() : $routes;
		foreach ($routes as $route) {
			if ($params = $route->matches($request)) {
				return [
					'params' => $params,
					'route' => $route,
				];
			}
		}

		return NULL;
	}

}