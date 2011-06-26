<?php

class Main extends Ctrl
{
	function index()
	{
		$data['app'] = array('name' => 'Vevui First App');
		$this->render('main_index', $data);
	}
}
