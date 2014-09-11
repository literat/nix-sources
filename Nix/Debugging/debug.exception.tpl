<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

header('HTTP/1.1 500 Internal Server Error');
header('Content-type: text/html');
function getHighlightedCode($filename, $line, $lines = 15, $id = false, $hide = false)
{
	$start = max(floor($line - ($lines / 2)), 0);
	$file = preg_replace("#\r(?!\n)|(?<!\r)\n#", "\r\n", file_get_contents($filename));
	$file = highlight_string($file, true);

	$file = explode("\n", $file);
	if($hide) {
		echo "<p><a href='javascript:toggle($id)' id='a-$id'>source</a>&nbsp;&nbsp;<strong>File: </strong>$filename&nbsp;&nbsp;<strong>Line: </strong>$line</p>";
	} else {
		echo "<p><strong>File: </strong>$filename&nbsp;&nbsp;<strong>Line: </strong>$line</p>";
	}

	if($hide) {
		echo "<div id='block-$id' style='display: none'>";
	}

	echo '<pre>', array_shift($file);
	$file = explode('<br />', $file[0]);

	for($i = $start; $i > 0; $i--) {
		if(preg_match('#.*(</?span[^>]*>)#', $file[$i], $match)) {
			if($match[1] != '</span>') {
				echo $match[1];
			}

			break;
		}
	}

	$maxLength = strlen($start + $lines) + 1;
	$file = array_slice($file, $start, $lines, true);
	foreach($file as $k => $v)	{
		if($k + 1 == $line) {
			$span = preg_replace("#[^>]*(<[^>]+>)[^<]*#", '$1', $v);
			printf("<span class='cur-line'><span class='line'>%{$maxLength}s</span> %s</span>%s", $k + 1, strip_tags($v), $span);
		} else {
			printf("<span class='line'>%{$maxLength}s</span> %s", $k + 1, $v);
		}
	}
	echo '</span></code></pre>';

	if($hide) {
		echo "</div>";
	}
}


if($exception instanceof FatalErrorException) {
	$title = $exception->getErrorTitle();
} else {
	$title = get_class($exception);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,noarchive" />
<meta name="generator" content="Nix Framework" />
<title><?php echo $title ?></title>
<style type="text/css" media="screen">
	#debug-panel {
		background: white;
		font-family: Calibri, sans-serif;
		font-size: 14px;
		margin: 0;
		padding: 1.5em;
	}
	#debug-panel .header {
		background: white;
		padding: 0 0 1em 0;
	}
	#debug-panel .header h1 {
		color: #444;
		margin: 0;
		padding: 0;
	}
	#debug-panel .header p {
		margin: 0;
		color: #ff0000;
	}
	#debug-panel .block {
		background: #efefef;
		margin: 0 0 1em 0;
		padding: 1em;
	}
	#debug-panel .block pre {
		border: 1px solid #c4c4c4;
	}
	#debug-panel .block p {
		padding: 4px 2px;
		margin: 0;
	}
	#debug-panel h2 {
		margin-bottom: .2em;
	}
	#debug-panel ol {
		padding: 0 0 0 1.5em;
		margin: 0;
	}
	#debug-panel code {
		font-family: Consolas, 'Lucida Console', 'Monaco CE', fixed, monospace;
		font-size: 14px;
	}
	#debug-panel pre {
		background: #fcffcf;
		padding: 2px;
		margin: 0;
		overflow: auto;
	}
	#debug-panel a {
		color: #4e851e;
	}
	#debug-panel span.line {
		color: black;
		margin: 0 8px 0 4px;
	}
	#debug-panel span.cur-line {
		background: #ff0000;
		color: white;
		display: block;
	}
</style>
<script type="text/javascript">
function toggle(id)
{
	block = document.getElementById('block-' + id);

	if (block.style.display == 'none') {
		block.style.display = 'block';
	} else {
		block.style.display = 'none';
	}
}
</script>
</head>
<body>
<div id="debug-panel">
	<div class="header">
		<h1><?php echo $title ?></h1>
		<p><?php echo htmlspecialchars($exception->getMessage()); ?></p>
	</div>

	<div class="block">
	<?php

		$i = -1;
		$trace = $exception instanceof Nix\Debugging\FatalErrorException ? $exception->getFatalTrace() : $exception->getTrace();
		foreach($trace as $i => $item) {
			if(isset($item['file']) && strpos($item['file'], dirname(dirname(__FILE__))) === false) {
				break;
			}
		}

		if(empty($trace[$i]) || !isset($trace[$i]['file'])) {
			foreach($trace as $i => $item) {
				if(isset($item['file'])) {
					break;
				}
			}
		}

		getHighlightedCode($trace[$i]['file'], $trace[$i]['line']);
	?>
	</div>

	<h2>Trace</h2>
	<div class="block">
		<ol>
		<?php
		for($n = 0, $total = count($trace) - 1; $n <= $total; $n++) {
			$t = $trace[$total - $n];

			echo '<li>';
			if(isset($t['file'])) {
				getHighlightedCode($t['file'], $t['line'], 7, $n, true);
			} else {
				echo "<p><strong>PHPInline:</strong> ";
				if(!empty($t['class'])) {
					echo $t['class'] . '::';
				}
				echo $t['function'] . '()</p>';
			}
			echo '</li>';
		}

		?>
		</ol>
	</div>

	<div class="block" style="font-size: 12px">
		server: <strong><?php echo $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] ?></strong><br />
		generated at: <strong><?php echo date('Y-m-d H.i') ?></strong><br />
		powered by <?php echo Nix\Application\Application::getFrameworkInfo(false) ?><br />
	</div>
</div>
<?php echo $rendered ?>
</body>
</html>