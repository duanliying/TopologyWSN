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

$f = "";
if(isset($_GET['f']))
	$f = htmlentities($_GET['f'], ENT_QUOTES);
else
	exit;

$type = intval($_GET['t']);

if($type == 1) {
	$r = array();
	$lines = file($f);
	foreach ($lines as $linevalue)
	{
		$t = explode(" ", $linevalue);
		$r[str_replace('"', '', $t[0])] = $t[1];
	}
	ksort($r);
	
	foreach($r as $k => $v) {
		echo floatval($v);
		echo "<br/>";
	}
}
else if($type == 2) {
	header("Content-type: application/vnd.ms-excel"); 
    header("Content-disposition: attachment; filename=\"graph.csv\"");
	$lines = file($f);
	foreach ($lines as $linevalue)
	{
		$t = explode(";", $linevalue);
		echo $t[0].";".$t[1];
		echo "\n";
	}
}
else if($type == 3) {
	$lines = file($f);
    $nb_sensors = 0;
    $sensors = "";
    $maxx = 0;
    $maxy = 0;
	foreach ($lines as $linevalue)
	{
    	$t = explode(",", $linevalue);
        $sx = round($t[0]);
        $sy = round($t[1]);
        if($sx > 0 && $sy > 0) {
            $nb_sensors++;
            $sensors .= "\n";
            $sensors .= '**.node['.($nb_sensors-1).'].mobility.x = '.$sx;
            $sensors .= "\n";
            $sensors .= '**.node['.($nb_sensors-1).'].mobility.y = '.$sy;
            $sensors .= "\n";
            if($sx > $maxx) $maxx = $sx;
            if($sy > $maxy) $maxy = $sy;
        }
	}

    $omnet_config = file_get_contents("./ressources/omnetpp.ini.tpl");
    $omnet_config = str_replace("{MAXX}", $maxx+100, $omnet_config);
    $omnet_config = str_replace("{MAXY}", $maxy+100, $omnet_config);
    $omnet_config = str_replace("{NB_SENSORS}", $nb_sensors, $omnet_config);
    $omnet_config = str_replace("{SENSORS}", $sensors, $omnet_config);

    $omnetpp_zip = './_downloads/WSNRouting.zip';
    @unlink($omnetpp_zip);
    @unlink("./_downloads/WSNRouting/omnetpp.ini");

    $conf = fopen('./_downloads/WSNRouting/omnetpp.ini', 'a');
    fputs($conf, $omnet_config);
    fclose($conf);

    $zip = new ZipArchive();
    $zip->open($omnetpp_zip, ZipArchive::CREATE);
    $zip->addFile('./_downloads/WSNRouting/omnetpp.ini', 'omnetpp.ini');
    $zip->addFile('./_downloads/WSNRouting/config.xml', 'config.xml');
    $zip->addFile('./_downloads/WSNRouting/flooding.anf', 'flooding.anf');
    $zip->addFile('./_downloads/WSNRouting/Makefile', 'Makefile');
    $zip->addFile('./_downloads/WSNRouting/Nic802154_TI_CC2420_Decider.xml', 'Nic802154_TI_CC2420_Decider.xml');
    $zip->addFile('./_downloads/WSNRouting/probabilisticBcast.anf', 'probabilisticBcast.anf');
    $zip->addFile('./_downloads/WSNRouting/README', 'README');
    $zip->addFile('./_downloads/WSNRouting/runConvergecast.sh', 'runConvergecast.sh');
    $zip->addFile('./_downloads/WSNRouting/runFlooding.sh', 'runFlooding.sh');
    $zip->addFile('./_downloads/WSNRouting/runProbabilisticBcast.sh', 'runProbabilisticBcast.sh');
    $zip->addFile('./_downloads/WSNRouting/WSNRouting.ned', 'WSNRouting.ned');
    $zip->addEmptyDir('results');
    $zip->close();

    preg_match("#data/([0-9]{10})(.+)\-sensors\.data#isU", $f, $out);

	include("./db/info/".$out[1].".inc.php");

    header('Content-disposition: attachment; filename=WSNRouting_'.str_replace('.osm', '', str_replace('.xml', '', $tinfo)).$out[2].'.zip'); 
    header('Content-Type: application/force-download'); 
    header('Content-Transfer-Encoding: binary');  
    header('Content-Length: '.filesize($omnetpp_zip)); 
    header('Pragma: no-cache'); 
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0'); 
    header('Expires: 0');
    readfile($omnetpp_zip);
}
?>