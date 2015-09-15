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

include "./_config.inc.php";
include "./parse_net.function.php";
$absolute_path = str_replace("index.php", "", realpath("./index.php"));

$osm = "";
if(isset($_GET['osm']))
	$osm = intval($_GET['osm']);

$propagation = "";
if(isset($_GET['propagation']))
	$propagation = intval($_GET['propagation']);

$nbsensors = "";
if(isset($_GET['nbsensors']))
	$nbsensors = intval($_GET['nbsensors']);

$wlanes = "";
if(isset($_GET['wlanes']))
	$wlanes = intval($_GET['wlanes']);

$modif = "";
if(isset($_GET['modif']))
	$modif = intval($_GET['modif']);

$lbackground = "";
if(isset($_GET['lbackground']))
	$lbackground = intval($_GET['lbackground']);

if(!file_exists("./db/data/$osm-junctions.data") || !file_exists("./db/data/$osm-edges.data")) {
	parse_net($osm);
}

shell_exec("export SUMO_HOME=".SUMO_HOME." && ".PYTHON." py/launch.py --identifiant $osm --modif $modif --propagation $propagation --nbsensors $nbsensors --wlanes $wlanes > ".$absolute_path."logs/$osm.log 2>>".$absolute_path."logs/_errors.log".($lbackground == 1 ? ' &' : ''));
?>


