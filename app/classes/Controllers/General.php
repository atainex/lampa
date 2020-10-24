<?php

namespace App\Controllers;

use Lampa\Core as Core;
use Lampa\Controller as Controller;
use Lampa\View as View;
use Lampa\Debug as Debug;
use Lampa\Config as Config;
use Lampa\Model as Model;

class General extends \Lampa\Template
{
	public $template = 'general.php';

	public function before() {
		parent::before();
	}
}