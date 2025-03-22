<?php

namespace Lampa;

class Route
{
	protected static $_routes = [];
	
	const REGEX_GROUP   = '\(((?:(?>[^()]+)|(?R))*)\)';
	const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';
	const REGEX_SEGMENT = '[^/.,;?\n]++';
	const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';
	
	protected $filters = [];
	protected $defaults = ['action' => 'index'];
	protected $routeRegex;
	protected $fileEte = [
		'filePath' => null,
		'ifNotFound' => null,
	];
	protected $uri;
	
	public static function set($name, $uri = NULL, $regex = NULL) {
		return Route::$_routes[$name] = new Route($uri, $regex);
	}
	
	public function __construct($uri = NULL, $regex = NULL) {
		if ($uri === NULL) {
			return;
		}
		if (!empty($uri)) {
			$this->uri = $uri;
		}
		if (!empty($regex)) {
			$this->_regex = $regex;
		}
		$this->routeRegex = Route::compile($uri, $regex);
	}
	
	public function runFile($path, $ifNotFound = null) {
		$this->fileEte['filePath'] = $path;
		$this->fileEte['ifNotFound'] = $ifNotFound;
		return $this;
	}
	
	public function getFileEte() {
		return $this->fileEte['filePath'];
	}

	public function getIfNotFoundFileEte() {
		return $this->fileEte['ifNotFound'];
	}

    public function getDefaultValue(string $key)
    {
        return $this->defaults[$key];
    }

	public function defaults(array $defaults = NULL) {
		if ($defaults === NULL) {
			return $this->defaults;
		}
		$this->defaults = $defaults;
		return $this;
	}
	
	public static function all() {
		return Route::$_routes;
	}
	
	public static function compile($uri, array $regex = NULL) {
		$expression = preg_replace('#'.Route::REGEX_ESCAPE.'#', '\\\\$0', $uri);

		if (strpos($expression, '(') !== FALSE) {
			$expression = str_replace(['(', ')'], ['(?:', ')?'], $expression);
		}
		$expression = str_replace(['<', '>'], ['(?P<', '>'.Route::REGEX_SEGMENT.')'], $expression);

		if ($regex) {
			$search = $replace = [];
			foreach ($regex as $key => $value)
			{
				$search[]  = "<$key>".Route::REGEX_SEGMENT;
				$replace[] = "<$key>$value";
			}
			$expression = str_replace($search, $replace, $expression);
		}

		return '#^'.$expression.'$#uD';
	}
	
	public function matches(Request $request) {
		$uri = trim($request->uri(), '/');

		if (!preg_match($this->routeRegex, $uri, $matches)) {
			return FALSE;
		}

		$params = [];
		foreach ($matches as $key => $value) {
			if (is_int($key)) {
				continue;
			}
			$params[$key] = $value;
		}

		foreach ($this->defaults as $key => $value) {
			if ( ! isset($params[$key]) OR $params[$key] === '') {
				$params[$key] = $value;
			}
		}

		if (!empty($params['controller'])) {
			$params['controller'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['controller'])));
		}

		if (!empty($params['directory'])) {
			$params['directory'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['directory'])));
		}

		if ($this->filters) {
			foreach ($this->filters as $callback) {
				$return = call_user_func($callback, $this, $params, $request);
				if ($return === FALSE) {
					return FALSE;
				}
				elseif (is_array($return)) {
					$params = $return;
				}
			}
		}

		return $params;
	}
}
