<?php

//
//    Copyright (C) 2014 Faye Sébastien <m@sfaye.com>
//    
//    This program is free software; you can redistribute it and/or
//    modify it under the terms of the GNU Lesser General Public
//    License as published by the Free Software Foundation; either
//    version 2.1 of the License, or (at your option) any later version.
//    
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
//    Lesser General Public License for more details.
//    
//    You should have received a copy of the GNU Lesser General Public
//    License along with this program; if not, write to the Free Software
//    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
//

$osm = "";
if(isset($_GET['osm']))
	$osm = intval($_GET['osm']);

echo "<b>Current tasks:</b> ";

$nbtasks = 0;
$handle_nbtasks=@opendir("./db/locks/");
while($tmp_nbtasks = readdir($handle_nbtasks))
{
	if(preg_match("#".$osm."#i", $tmp_nbtasks)) 
	{
		$nbtasks++;
	}
}
if($nbtasks == 0) {
	echo $nbtasks.".";
}

echo "<br/><br/><b>Available results:</b><div style='width: 100%; text-align: center; padding-top: 5px; padding-bottom: 10px;'><i>(communication range, intersections, deployment 1, deployment 2)</i></div>";

$nbtasks = 0;
$handle_nbtasks=@opendir("./db/output/");
while($tmp_nbtasks = readdir($handle_nbtasks))
{
	if(preg_match("#".$osm."(.+)csv$#i", $tmp_nbtasks)) 
	{
		$tmp_nbtasks = str_replace("--2.csv", "-b.csv", $tmp_nbtasks);
		$tmp_nbtasks = str_replace("--1.csv", "-f.csv", $tmp_nbtasks);
		if(preg_match("#[0-9]{5,}\-[1-9]#isU", $tmp_nbtasks))
			$boola = "3: ";
		else
			$boola = "";
		$tmp_nbtasks = str_replace("--1", "-2", $tmp_nbtasks);
		$tmp_nbtasks = str_replace("-0-", "-1-", $tmp_nbtasks);
		$tmp_nbtasks = str_replace(".csv", "", $tmp_nbtasks);

		$tab = explode("-", $tmp_nbtasks);
		echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".str_replace("10000000", "infinite", str_replace("b", "IEEE 802.15.2-2003", str_replace("f", "free space", $tab[4])))." ; ".$tab[2]." ; ".$tab[3]." ; ".$boola.$tab[1].".<br/>";
		$nbtasks++;
	}
}
if($nbtasks == 0) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<i>no data.</i>";
}
?>