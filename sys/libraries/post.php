<?php

class Post extends Lib
{
	private $_post;
	private $_rules;
	private $_error;

	function  __construct()
	{
		$this->_post = &$_POST;
		$this->_rules = array();
		$this->_error = array();
	}

	function __get($name)
	{
		return array_key_exists($name, $this->_post)?$this->_post[$name]:NULL;
	}

	function rule($name, $required, $_ = NULL)
	{
		$this->_rules[] = array($name, $required, $_?array_slice(func_get_args(), 2):array());
	}

	function check($all = FALSE)
	{
		foreach($this->_rules as $rule)
		{
			$name = $rule[0];
			$required = $rule[1];
			$funcs = $rule[2];

			if ( $required && ((!array_key_exists($name, $this->_post)) || (''===$this->_post[$name])) )
			{
				$this->_error[] = $name;
				if (!$all) return FALSE;
			}

			if (array_key_exists($name, $this->_post))
			{
				$param = $this->_post[$name];
				foreach($funcs as $func)
				{
					$res = call_user_func($func, $param);
					if (is_bool($res))
					{
						if (!$res)
						{
							$this->_error[] = $name;
							if (!$all) return FALSE;
						}
					}
					else
					{
						$param = $res;
					}
				}
				$this->_post[$name] = $param;
			}
		}
		return empty($this->_error);
	}
}

/* End of file sys/libraries/post.php */