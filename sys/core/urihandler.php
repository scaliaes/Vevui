<?php

class Vevui_URIHandler
{
	var $request_class;
	var $request_method;
	var $request_params;

	function __construct()
	{
		$uri_segs = explode('/', $_SERVER['REQUEST_URI']);
		$uri_segs_count = count($uri_segs);

		$start= ($uri_segs[1] === 'index.php')?2:1;
		$this->request_class = 'sample';
		$this->request_method = 'index';
		$this->request_params = array();

		if ($uri_segs[$start])
			$this->request_class = $uri_segs[$start];

		++$start;
		if ($start < $uri_segs_count)
		{
			if ($uri_segs[$start])
				$this->request_method = $uri_segs[$start];

			$this->request_params= array_slice($uri_segs, $start+1);
		}
	}
}

/* End of file sys/core/urihandler.php */
