<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

?>
<style type="text/css">
#nixToolbar {
	position: absolute;
	right: 5px;
	bottom: 5px;
	z-index: 10;
}

#nixContainer {
	position: relative;
	color: #eee;
	background: #222;
	border-collapse: collapse;
	border: 1px solid black;
	font: normal normal 11px/1.4 Consolas, 'Lucida Console', 'Monaco CE', fixed, monospace;
	margin: 0;
	padding: 0;
	opacity: .20;
	width: 300px;
	text-align: left;
}

#nixContainer:hover {
	opacity: 1;
}

#nixContainer th, #nixContainer td {
	padding: 1px 2px;
}

#nixContainer tr:hover td {
	background: #555;
}

#nixContainer th {
	background: #fff;
	color: #000;
}

#nixToolbar abbr {
	border: none;
	letter-spacing: inherit;
	text-transform: inherit;
	font-size: inherit;
}
</style>


<div id="nixToolbar" ondblclick="this.style.display='none';">
<table id="nixContainer">
<?php

ksort(self::$toolbar);
foreach (self::$toolbar as $group => $message) {

	if (!empty($group))
		echo '<tr><th>' . strtoupper($group) . ':</th></tr>';

	foreach ($message as $m)
		echo "<tr><td>$m</td></tr>";
}

?>
</table>
</div>