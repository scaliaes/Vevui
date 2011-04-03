<?php

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