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

$type = "";
if(isset($_GET['type']))
	$type = intval($_GET['type']);

$modif = "";
if(isset($_GET['modif']))
	$modif = intval($_GET['modif']);

$paramsup = "";
if(isset($_GET['paramsup']))
	$paramsup = intval($_GET['paramsup']);
$paparamsup = ($paramsup != "" ? " --paramsup $paramsup" : "");

echo shell_exec(PYTHON." ".$absolute_path."py/analysis.py --identifiant $osm --modif $modif --propagation $propagation --nbsensors $nbsensors --wlanes $wlanes --analysistype $type$paparamsup 2>>".$absolute_path."logs/_errors.log");

if($type == 300) {
    $splot_data = $absolute_path."/db/splots/".$osm."-".$modif."-".$wlanes."-".$nbsensors."-".$propagation.".data";
    $splot_png = $absolute_path."/db/splots/".$osm."-".$modif."-".$wlanes."-".$nbsensors."-".$propagation.".png";

    if(!file_exists($splot_png)) {
    	echo "<div style='display:none;'>";
        $commande = 'set surface;set dgrid3d 30,30;set hidden3d;set contour base;set ticslevel 0.8;set terminal png size 600,500 enhanced font "Helvetica,10";set output "'.$splot_png.'";splot "'.$splot_data.'" with pm3d title ""';
        shell_exec(GNUPLOT." -e '$commande'");
    	echo "</div>";
    }

    echo "<img src='./db/splots/".$osm."-".$modif."-".$wlanes."-".$nbsensors."-".$propagation.".png'/>";
}
?>


