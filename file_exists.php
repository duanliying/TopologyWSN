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

if(file_exists("./db/output/$osm-$modif-$wlanes-$nbsensors-$propagation.csv"))
	echo '1';
else if(file_exists("./db/locks/$osm-$wlanes-$nbsensors.lock") || file_exists("./db/locks/$osm-$wlanes-$nbsensors-$propagation.lock") || file_exists("./db/locks/$osm-$modif-$wlanes-$nbsensors-$propagation.lock"))
	echo '2';
else
	echo '0';
?>