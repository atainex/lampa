<?php

namespace App\Controllers\General;

use Lampa\Core as Core;
use Lampa\Controller as Controller;
use Lampa\View as View;
use Lampa\Debug as Debug;
use Lampa\Config as Config;
use Lampa\Model as Model;
use App\Controllers\General;

class Main extends General
{
	public function index()
	{
		$exampleVar = 'Hello world!';

		$this->template->title = 'Заголовок вкладки';
		$this->template->content = View::factory('main.php')->set('exampleField', $exampleVar);
	}
}