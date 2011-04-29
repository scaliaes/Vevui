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

function vevui_shutdown_error_handler($error)
{
	print_r($error);
	$is_error = false;
	switch($error['type'])
	{
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
		case E_PARSE:
		case E_RECOVERABLE_ERROR:
		case E_WARNING:
			$is_error = true;
			break;
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_STRICT:
			break;
	}

	if ($is_error)
	{
		$file_contents = file_get_contents($error['file']);
		$nlines = count(file($error['file']));

		$range = range(max(1, $error['line']-5), min($nlines, $error['line']+5));

		$lines_array = preg_split('/<[ ]*br[ ]*\/[ ]*>/', highlight_string($file_contents, TRUE));
		$highlighted = '';
		$line_nums = '';
		foreach($range as $i)
		{
			if ($i == ($error['line']))
			{
				$line_nums .= '<div style="background-color: #ff9999">';
	//				$highlighted .= '<div style="background-color: #ff9999">';
			}
			$line_nums .= $i.'<br/>';
			$highlighted .= $lines_array[$i-1].'<br/>';
			if ($i == ($error['line']))
			{
				$line_nums .= '</div>';
	//				$highlighted .= '</div>';
			}
		}

		echo '<html>
				<head><title>Fatal error</title></head>
				<body>', "<h2>{$error['message']}</h2>\n",
					'<style type="text/css">
						.num {
						float: left;
						color: gray;
	//						font-size: 13px;
	//						font-family: monospace;
						text-align: right;
						margin-right: 6pt;
						padding-right: 6pt;
						border-right: 1px solid gray;}

						body {margin: 0px; margin-left: 5px;}
						td {vertical-align: top;}
						code {white-space: nowrap;}
					</style>',
					"<table><tr><td class=\"num\">\n$line_nums\n</td><td>\n$highlighted\n</td></tr></table>",
				'</body>
			</html>';
		//echo "Script execution halted ({$error['message']})";
	}
}

/* End of file sys/core/errorhandler.php */
