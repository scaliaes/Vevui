<?php

class Ctrl
{
	private $_model_loader;
	private $_helper_loader;
	
	function __get($prop_name)
	{
        switch ($prop_name)
        {
			case 'm':
				if(null == $this->_model_loader)
				{
					require(SYS_PATH.'/core/modelloader.php');
					$this->_model_loader = new ModelLoader();
				}

				return $this->_model_loader;
			case 'h':
				if(null == $this->_helper_loader)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader = new HelperLoader();
				}
				
				return $this->_helper_loader;
			default:
				return null;
		}
    }

	function render($view_name, $vars = array())
	{
		Haanga::Load($view_name.'.html', $vars);
	}
}

/* End of file sys/core/ctrl.php */