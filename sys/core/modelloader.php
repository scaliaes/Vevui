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

class ModelLoader
{
	function __construct()
	{
		$core = & Vevui::get();
		$loadnosqlmdl = $loadsqlmdl = FALSE;

		foreach($core->e->db->databases as $db)
		{
			if (!$loadnosqlmdl)
			{
				switch($db->drv)
				{
					case 'mongodb':
					case 'redis':
						$loadnosqlmdl = TRUE;
				}
			}
			if (!$loadsqlmdl)
			{
				switch($db->drv)
				{
					case 'mysql':
					case 'sqlite3':
						$loadsqlmdl = TRUE;
				}
			}
		}

		if ($loadnosqlmdl) require(SYS_PATH.'/core/mdl.php');
		if ($loadsqlmdl) require(SYS_PATH.'/core/sqlmdl.php');
	}

	function __get($model_name)
	{
		$data = Vevui::get_installation_data();
		$data = isset($data['m'][$model_name]) ? $data['m'][$model_name] : NULL;

		require(APP_MODELS_PATH.'/'.$model_name.'.php');
		return $this->{$model_name} = new $model_name($data);
	}
}

/* End of file sys/core/modelloader.php */
