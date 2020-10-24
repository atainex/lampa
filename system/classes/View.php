<?php

namespace Lampa;

class View
{
	protected $__path = null;
	protected $__contents = array();
	protected $__content = null;
	
	public static function factory($path) {
		return new self($path);
	}
	
	public function __construct($path = null) {
		$this->setPath($path);
	}

	public function setPath($path) {
		if (!empty($path)) {			
			if (!file_exists(DOCROOT.'app/views/'.$path)) {
				die('Файл шаблона не найден');
			}
			$this->__path = DOCROOT.'app/views/'.$path;
		}
	}
	
	public function set($key, $value) {
		if (is_array($key) OR $key instanceof Traversable) {
			foreach ($key as $name => $value) {
				$this->__contents[$name] = $value;
			}
		} else {
			$this->__contents[$key] = $value;
		}
		return $this;
	}
	
	public function __set($key, $value) {
		$this->set($key, $value);
	}
	
	public function &__get($key) {
		if (array_key_exists($key, $this->__contents)) {
			return $this->__contents[$key];
		}
	}

	public function __isset($key) {
		return isset($this->__contents[$key]);
	}

	public function __unset($key) {
		unset($this->__contents[$key]);
	}
	
	public function render() {
		foreach ($this->__contents as $k => $v) {
			if (is_object($this->__contents[$k])) {							
				if (is_a($this->__contents[$k], 'Lampa\View')) {
					$this->__contents[$k] = $v->render();
				}
			}
		}
		extract($this->__contents, EXTR_SKIP | EXTR_REFS);
		ob_start();
		if (file_exists($this->__path)) {
			require($this->__path);
		} else {
			die('Файл шаблона «'.$this->__path.'» не найден');
		}
		$this->__content = ob_get_contents();
		ob_end_clean();
		return $this->__content;
	}
}