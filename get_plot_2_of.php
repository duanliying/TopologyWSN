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
	$osm = str_replace(",","-",htmlentities($_GET['osm'], ENT_QUOTES));
$osmlist = explode("-", $osm);
$osmlistregex = str_replace("-", "|", $osm);
sort($osmlist, SORT_NUMERIC);

$minok = 999;
$maxok = -999;
$handle = opendir("./db/data/");
while($file = readdir($handle)) {
	$out = array();
	if(preg_match("#(".$osmlistregex.")\-([0-9-]+)\-.+sensors#", $file, $out)) {
		$i = intval($out[2]);
		if($minok > $i) $minok = intval($i);
		if($maxok < $i) $maxok = intval($i);
	}
}
closedir($handle);

if($minok == 999 || $maxok == -999) {
	echo "<img src='./ressources/images/not-enough-data.png'/>";
	exit;
}

if(preg_match("#\-#", $osm)) {
	$hashosm = implode("", $osmlist);
	$hashosm = substr(md5($hashosm), 0, 10);
}
else 
	$hashosm = $osm;

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

$ntype = "";
$label = "";

if($type == 2) {
	$ntype = "nodes";
	$label = "nodes";
}
else if($type == 3) {
	$ntype = "nodes_bound";
	$label = "nodes per square kilometer";
}

else if($type == 6) {
	$ntype = "cliques";
	$label = "cliques";
}
else if($type == 7) {
	$ntype = "max_clique";
	$label = "nodes in the maximum clique graph";
}
else if($type == 9) {
	$ntype = "$paramsup-cliques";
	$label = "$paramsup-cliques";
}
else if($type == 12) {
	$ntype = "connected_components";
	$label = "connected components";
}
else if($type == 13) {
	$ntype = "connected_components_diameter";
	$label = "connected component diameter (hops)";
}
else if($type == 14) {
	$ntype = "connected_components_diameterm";
	$label = "connected component diameter (meters)";
}
else if($type == 16) {
	$ntype = "connected_components_ap";
	$label = "connected component articulation points";
}
else if($type == 17) {
	$ntype = "degree";
	$label = "average degree";
}
else if($type == 18) {
	$ntype = "clustering";
	$label = "average clustering coefficient";
}
else if($type == 19) {
	$ntype = "betweeness_centrality";
	$label = "average betweeness centrality";
}
else if($type == 43) {
	$ntype = "mcc_closeness_centrality";
	$label = "closeness centrality";
}
else if($type == 20) {
	$ntype = "pagerank";
	$label = "average pagerank";
}
else if($type == 35) {
	$ntype = "biconnected_components";
	$label = "biconnected components";
}
else if($type == 36) {
	$ntype = "biconnected_components_diameter";
	$label = "biconnected component diameter (hops)";
}
else if($type == 37) {
	$ntype = "biconnected_components_diameterm";
	$label = "biconnected component diameter (meters)";
}
else if($type == 38) {
	$ntype = "connected_components_bound";
	$label = "components per square kilometer";
}
else if($type == 39) {
	$ntype = "biconnected_components_bound";
	$label = "components per square kilometer";
}
else if($type == 40) {
	$ntype = "connected_components_p";
	$label = "components / total nodes";
}
else if($type == 41) {
	$ntype = "biconnected_components_p";
	$label = "components / total nodes";
}
else if($type == 42) {
	$ntype = "1_connected_components";
	$label = "% of components (compared to total components)";
}
else if($type == 46) {
	$ntype = "connected_components_diameterp";
	$label = "connected component diameter (weights)";
}
else if($type == 47) {
	$ntype = "mcc_diameterp";
	$label = "connected component diameter (weights)";
}
else if($type == 21) {
	$ntype = "mcc_nodes";
	$label = "nodes";
}
else if($type == 22) {
	$ntype = "mcc_nodes_p";
	$label = "% of nodes (compared to total nodes)";
}
else if($type == 23) {
	$ntype = "mcc_cliques";
	$label = "cliques";
}
else if($type == 24) {
	$ntype = "mcc_max_clique";
	$label = "nodes in the maximum clique graph";
}
else if($type == 25) {
	$ntype = "mcc_$paramsup-cliques";
	$label = "$paramsup-cliques";
}
else if($type == 27) {
	$ntype = "mcc_diameter";
	$label = "connected component diameter (hops)";
}
else if($type == 28) {
	$ntype = "mcc_diameterm";
	$label = "connected component diameter (meters)";
}
else if($type == 30) {
	$ntype = "mcc_ap";
	$label = "connected component articulation points";
}
else if($type == 31) {
	$ntype = "mcc_degree";
	$label = "average degree";
}
else if($type == 32) {
	$ntype = "mcc_clustering";
	$label = "average clustering coefficient";
}
else if($type == 33) {
	$ntype = "mcc_betweeness_centrality";
	$label = "average betweeness centrality";
}
else if($type == 34) {
	$ntype = "mcc_pagerank";
	$label = "average pagerank";
}



$plot_file_png = "./db/plots/$hashosm-$wlanes-$nbsensors-$propagation-$ntype-vs$minok-$maxok";

if(!file_exists($plot_file_png.".png")) {
	foreach($osmlist as $v) {
			if(!file_exists("./db/plots/$v-$wlanes-$nbsensors-$propagation-$ntype-vs$minok-$maxok.dat")) {
				$res = Array();
				for($i = $minok; $i <= $maxok; $i++) {
					if(file_exists("./db/output/$v-$i-$wlanes-$nbsensors-$propagation-$ntype")) {
						$lines = file("./db/output/$v-$i-$wlanes-$nbsensors-$propagation-$ntype");
						$res[$i] = floatval($lines[0]);
					}
				}

				$pfich = fopen("./db/plots/$v-$wlanes-$nbsensors-$propagation-$ntype-vs$minok-$maxok.dat", 'a');
				foreach($res as $resk => $resv) {
					$list = "$resk $resv";
					$list .= "\n";
					fputs($pfich, $list);	
				}
				fclose($pfich);
			}
	}
	
	echo "<div style='display:none;'>";

		$pos = "center";
		$plot = "";
		$i = 0;
		foreach($osmlist as $v) {
			if($i > 0) $plot .= ',';	
			include ("./db/info/$v.inc.php");
			$method = preg_replace("#\.(osm|net|xml|osm\.xml|net\.xml)$#i", "", $tinfo);
			$plot .= '"./db/plots/'.$v.'-'.$wlanes.'-'.$nbsensors.'-'.$propagation.'-'.$ntype.'-vs'.$minok.'-'.$maxok.'.dat" using 1:2 with linespoints lw 4 ps 1.5 title "'.$method.'"';
			$i++;
		}
		$labelx = "network modification";

		$commande = 'set xlabel "'.$labelx.'";set ylabel "'.$label.'";set autoscale'.$logscale.';set ytics auto;set xtics 1;set grid;set size 1,1;set terminal png;set key on inside left '.$pos.';set style fill solid 1.00 border -1;plot '.$plot.';set output "'.$plot_file_png.'.png";replot;set terminal postscript eps color enhanced "Helvetica" 20;set output "'.$plot_file_png.'.eps";replot';

	shell_exec(GNUPLOT." -e '$commande'");
	echo "</div>";
}

echo "<img src='".$plot_file_png.".png'/>";
echo "<br/><br/>";
echo "<hr/><a href='".$plot_file_png.".eps' target='_blank'>Download the EPS file</a> - Download the GNUPLOT files data: ";
	$i = 0;
	foreach($osmlist as $v) {
		if($i != 0) echo ", ";
		echo "<a href='./db/plots/$v-$wlanes-$nbsensors-$propagation-$ntype-vs$minok-$maxok.dat' target='_blank'>$v</a>";
		$i++;
	}
echo "<hr/>";
?>


