<?php
/*************************************************************************
 Copyright 2011 Vevui Development Team

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
*************************************************************************/

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
