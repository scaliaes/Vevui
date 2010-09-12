<?php

class Ctrl
{
	private $_model_loader;
	
	function __get($prop_name)
	{
        if('m' == $prop_name)
		{
			if(null == $this->_model_loader)
			{
				require(SYS_PATH.'/core/modelloader.php');
				$this->_model_loader = new ModelLoader();
			}
			
			return $this->_model_loader;
		}

		return null;
    }
}

/* End of file sys/core/ctrl.php */