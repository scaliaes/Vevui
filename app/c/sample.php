<?php

class Sample extends Ctrl
{
	function __construct()
	{
	}

	function index()
	{
		$vars['title'] = 'Sample view';
		$vars['app']['name'] = 'Vevui'.'=>'.$this->m->test_mdl->get_data();
		$this->render('index', $vars);
	}

	function params($par1 = null, $par2 = null, $par3 = null, $par4 = null, $par5 = null)
	{
		echo 'par1=',$par1,'.<br/>';
		echo 'par2=',$par2,'.<br/>';
		echo 'par3=',$par3,'.<br/>';
		echo 'par4=',$par4,'.<br/>';
		echo 'par5=',$par5,'.<br/>';
	}
}

/* End of file app/c/default.php */