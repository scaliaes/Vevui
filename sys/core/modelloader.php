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
		require(SYS_PATH.'/core/mdl.php');
    }

	function __get($model_name)
	{
		include(APP_PATH.'/m/'.$model_name.'.php');
		return $this->{$model_name} = new $model_name();
	}
}

/* End of file sys/core/modelloader.php */
