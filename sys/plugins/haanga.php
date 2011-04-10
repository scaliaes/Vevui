<?php

class Haanga_Extension_Tag_ElapsedTime
{
	public $is_block = FALSE;

	static function generator($cmp, $args, $assign=NULL)
	{
		/* ast */
		$code = hcode();

		/* llamar a la funcion */
		$exec = hexec('sprintf', '%.4f',
			hexpr( hexec('microtime', TRUE), '-', hvar('globals', 'start_time') )
		);

		/* imprimir la funcion */
		$cmp->do_print($code, $exec);

		return $code;
	}
}

/* End of file sys/plugins/haanga.php */