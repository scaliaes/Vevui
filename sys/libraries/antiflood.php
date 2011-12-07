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

class Antiflood extends Lib
{
	private $_conn;

	function  __construct()
	{
		parent::__construct();

		$servers = array();
		foreach($this->e->mcache['servers'] as $server)
		{
			$servers[] = array($server['host'], $server['port'], $server['weight']);
		}
		$this->_conn = new Memcached();
		$this->_conn->addServers($servers);
	}

	function get($name)
	{
		return $this->_conn->get($name);
	}

	function set($name, $value, $expiration = 0)
	{
		return $this->_conn->set($name, $value, $expiration);
	}
}

/* End of file sys/libraries/antiflood.php */
