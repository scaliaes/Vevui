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

class Drv_Redis extends Drv
{
	private $_connection;
	
	function __construct($db_config)
	{
		parent::__construct($db_config);
	
		try
		{
			$redis_port = property_exists($db_config, 'port') ? $db_config->port : 6379;
			$redis_timeout = property_exists($db_config, 'timeout') ? $db_config->timeout : 0;

			$this->_connection = new Redis();
			$this->_connection->connect($db_config->host, $redis_port, $redis_timeout) or $this->_raise_error('Can\'t connect to Redis server');
			
			if (property_exists($db_config, 'prefix'))
			{
				// use custom prefix on all keys
				$this->_->setOption(Redis::OPT_PREFIX, $db_config->prefix); 
			}			
		}
		catch (RedisException $e)
		{
			$this->_raise_error($e);
		}
	}

	function set_php_serializer()
	{
		// use built-in serialize/unserialize
		$this->_connection->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
	}
	
	function set_igbinary_serializer()
	{
		// use igBinary serialize/unserialize	
		$this->_connection->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
	}	

	function unset_serializer()
	{
		// don't serialize data
		$this->_connection->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
	}
	
	function __call($method, $args)
	{
		try
		{
			return call_user_func_array(array($this->_connection, $method), $args);
		}
		catch(RedisException $e)
		{
			$this->_raise_error($e);
		}
	}
}

/* End of file sys/core/drvs/redis.php */
