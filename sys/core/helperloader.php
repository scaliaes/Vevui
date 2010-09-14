<?php
	
class HelperLoader
{
	private $_helpers = array();
	
	function __get($helper_name)
	{
		if (!array_key_exists($helper_name, $this->_helpers))
		{
			include(SYS_PATH.'/helpers/'.strtolower($helper_name).'.php');
			$this->_helpers[$helper_name] = new $helper_name();
		}
		return $this->_helpers[$helper_name];
    }
}

/* End of file sys/core/helperloader.php */