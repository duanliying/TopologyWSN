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

function parse_net($osm) {
	$net_file = "./db/nets/$osm.net.xml";
	$xml = file_get_contents($net_file);
	
	$tab = array();
	$out = array();
	
	preg_match_all("#edge id=\"([^\"]+)\" from=\"([^\"]+)\" to=\"([^\"]+)\"#isU", $xml, $out);
	
	foreach($out[2] as $k => $v) {
	    if(!isset($tab[$v])) $tab[$v] = array(); 
	
		if(!isset($tab[$v]['from'])) $tab[$v]['from'] = 0;
		$tab[$v]['from']++;
	}
	
	foreach($out[3] as $k => $v) {
	    if(!isset($tab[$v])) $tab[$v] = array(); 
	
		if(!isset($tab[$v]['to'])) $tab[$v]['to'] = 0;
		$tab[$v]['to']++;
	}
	
	$i = 0;
	$liste_intersections = array();
	foreach($tab as $k => $v) {
		if($tab[$k]['from'] > 2 || $tab[$k]['to'] > 2) {
			$liste_intersections[$i] = $k;
			$i++;
		}
	}

	$outbis = array();
	preg_match_all("#junction id=\"([^\"]+)\" type=\"traffic_light\"#isU", $xml, $outbis);
	foreach($outbis[1] as $k => $v) {
		if(!in_array($v, $liste_intersections)) {
			$liste_intersections[$i] = $v;
			$i++;
		}
	}

	$i = 0;
	$liste_edges = array();
	foreach($out[1] as $k => $v) {
		if(in_array($out[2][$k], $liste_intersections) || in_array($out[3][$k], $liste_intersections)) {
			$liste_edges[$i] = $v;
			$i++;
		}
	}

	$file_save = "./db/data/$osm-junctions.data";
	if(file_exists($file_save)) @unlink($file_save);
	$file = fopen($file_save, 'a+');
	foreach($liste_intersections as $v) {
		fputs($file, $v."\n");
	}
	fclose($file);

	$file_save = "./db/data/$osm-edges.data";
	if(file_exists($file_save)) @unlink($file_save);
	$file = fopen($file_save, 'a+');
	foreach($liste_edges as $v) {
		fputs($file, $v."\n");
	}
	fclose($file);
}
?>