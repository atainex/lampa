<?php

namespace Lampa;

abstract class Template extends Controller
{
	public $template = null;
	public $autoRender = true;
	
	public function before() {
		parent::before();
		if ($this->autoRender === true) {
			$this->template = View::factory($this->template);
		}
	}
	
	public function after() {
		if ($this->autoRender === true) {
			echo $this->template->render();
			exit();
		}
		parent::after();
	}
}