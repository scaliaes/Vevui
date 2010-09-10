<?php
namespace Core
{
	class Ctrl
	{
		function __construct()
		{
			echo 'namespace=',__NAMESPACE__,'::',__CLASS__,'.';
		}
	}
}

namespace test
{
	$segs = explode('/', $_SERVER['REQUEST_URI']);

	$time= microtime(TRUE);

/*
	for($i=0;$i<10000000;++$i)
	{
		echo "hola$i";
		$start= ($segs[1] === 'index.php')?2:1;
	}
*/

	$start= ($segs[1] === 'index.php')?2:1;

	include_once 'test.php';
	$m = eval('return new \\'.$segs[$start].';');
	eval('$m->'.$segs[$start+1].'('.implode(',',array_slice($segs, $start+2)).');');
//	echo 'Done in ',(microtime(TRUE)-$time),' s.<br/>';

	/*
	ob_start();
	for($i=0;$i<10000000;++$i)
		echo 'clase=',$segs[$start],'<br/>'.
			'metodo=',$segs[$start+1],'<br/>'.
			'params=',implode(', ', array_slice($segs, $start+2)),".\n";
	ob_end_flush();
	*/
}

/* End of file index.php */
/* Location: ./pub/index.php */
