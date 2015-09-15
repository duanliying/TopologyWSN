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
sort($osmlist, SORT_NUMERIC);

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

$paramsup = "";
if(isset($_GET['paramsup']))
	$paramsup = htmlentities($_GET['paramsup'], ENT_QUOTES);

$type = "";
if(isset($_GET['type']))
	$type = intval($_GET['type']);

$modif = "";
if(isset($_GET['modif']))
	$modif = intval($_GET['modif']);

$ntype = "";
$label = "";
$boolhisto = true;


// Histograms

if($type >= 100) {
	$boolhisto = false;
}

else if($type == 2) {
	$ntype = "nodes";
	$label = "nodes";
}
else if($type == 3) {
	$ntype = "nodes_bound";
	$label = "nodes per square kilometer";
}
else if($type == 4) {
	$ntype = "bound";
	$label = "square kilometers";
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
else if($type == 20) {
	$ntype = "pagerank";
	$label = "average pagerank";
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
else if($type == 43) {
	$ntype = "mcc_closeness_centrality";
	$label = "closeness centrality";
}


// Frequences / CDF

$bool_frequence = false;
if($type >= 200) {
    $type -= 100;
    $bool_frequence = true;
}

if($type == 100) {
	$ntype = "cdf_cliques";
	$labelx = "clique size";
}
else if($type == 101) {
	$ntype = "cdf_connected_components";
	$labelx = "connected components size";
}
else if($type == 102) {
	$ntype = "cdf_degree";
	$labelx = "degree";
}
else if($type == 103) {
	$ntype = "cdf_connected_components_diameter";
	$labelx = "connected components diameter (hops)";
}
else if($type == 104) {
	$ntype = "cdf_connected_components_diameterm";
	$labelx = "connected components diameter (meters)";
}
else if($type == 105) {
	$ntype = "cdf_connected_components_ap";
	$labelx = "connected components articulation points";
}

else if($type == 106) {
	$ntype = "cdf_mcc_cliques";
	$labelx = "clique size";
}
else if($type == 107) {
	$ntype = "cdf_mcc_degree";
	$labelx = "degree";
}
else if($type == 108) {
	$ntype = "cdf_mcc_distance";
	$labelx = "distance (hops)";
}
else if($type == 109) {
	$ntype = "cdf_mcc_distancem";
	$labelx = "distance (meters)";
}
else if($type == 110) {
	$ntype = "cdf_edges_length";
	$labelx = "length (meters)";
}
else if($type == 111) {
	$ntype = "cdf_mcc_edges_length";
	$labelx = "length (meters)";
}
else if($type == 112) {
	$ntype = "cdf_ap_cc";
	$labelx = "% of articulation points (compared to components size)";
}
else if($type == 113) {
	$ntype = "cdf_betweeness_centrality";
	$labelx = "betweeness centrality";
}
else if($type == 114) {
	$ntype = "cdf_mcc_betweeness_centrality";
	$labelx = "betweeness centrality";
}
else if($type == 115) {
	$ntype = "cdf_connected_components_diameterp";
	$labelx = "connected components diameter (weights)";
}
else if($type == 116) {
	$ntype = "cdf_mcc_distancep";
	$labelx = "distance (weights)";
}
else if($type == 117) {
	$ntype = "cdf_closeness_centrality";
	$labelx = "closeness centrality";
}
else if($type == 118) {
	$ntype = "cdf_mcc_closeness_centrality";
	$labelx = "closeness centrality";
}
else if($type == 119) {
	$ntype = "cdf_nodes_p";
	$labelx = "% of nodes (compared to total nodes)";
}
else if($type == 120) {
	$ntype = "cdf_distance_cc";
	$labelx = "distance between connected components";
}
else if($type == 121) {
	$ntype = "cdf_min_distance_cc";
	$labelx = "minimum distance between connected components";
}
else if($type == 122) {
	$ntype = "cdf_clustering_coefficient";
	$labelx = "clustering coefficient";
}
else if($type == 123) {
	$ntype = "cdf_mcc_clustering_coefficient";
	$labelx = "clustering coefficient";
}
else if($type == 150) {
	$ntype = "cdf_delaunay_distance_cc";
	$labelx = "distance between connected components";
}


// Logscale

$logscale = "";
if($paramsup == "x" || $paramsup == "y" || $paramsup == "xy" || $paramsup == "yx") {
	$logscale = $paramsup;
	$paramsup = "";
	$plot_file_png = "./db/plots/$hashosm-$modif-$wlanes-$nbsensors-$propagation-$ntype$logscale";
	$plot_file_dat = "./db/plots/$hashosm-$modif-$wlanes-$nbsensors-$propagation-$ntype";
}
else {
	$plot_file_png = "./db/plots/$hashosm-$modif-$wlanes-$nbsensors-$propagation-$ntype$paramsup";
	$plot_file_dat = "./db/plots/$hashosm-$modif-$wlanes-$nbsensors-$propagation-$ntype$paramsup";
}

if($bool_frequence) {
    $plot_file_png .= "-freq"; 
    $plot_file_dat .= "-freq";

    $gogogo = false;
    foreach($osmlist as $v) {
       $gogogo = false;
        if(!file_exists('./db/plots/'.$v.'-'.$modif.'-'.$wlanes.'-'.$nbsensors.'-'.$propagation.'-'.$ntype.'-freq.png')) {
            $gogogo = true;
        }
    }
}

if((!$bool_frequence && !file_exists($plot_file_png.".png")) || ($bool_frequence && $gogogo)) {

	if($boolhisto) {
		@unlink($plot_file_dat.".dat");
		$pfich = fopen($plot_file_dat.".dat", 'a');
	}
	foreach($osmlist as $v) {
		if(!file_exists("./db/nets/$v.net.xml")) {
			shell_exec(SUMO_HOME."/bin/netconvert --osm-files ".$absolute_path."db/uploads/$v --osm.skip-duplicates-check false --keep-edges.postload true --geometry.remove --remove-edges.isolated --remove-edges.by-type highway.residential,highway.service,highway.unclassified,highway.living_street,highway.track,highway.pedestrian,highway.bus_guideway,highway.raceway,highway.footway,highway.bridleway,highway.steps,highway.path,highway.cycleway,highway.proposed,highway.construction,highway.escape,highway.emergency,highway.services,highway.phone,highway.emergency_access_point,highway.crossing,highway.bus_stop --remove-edges.by-vclass private,public_transport,public_emergency,public_authority,public_army,vip,ignoring,passenger,hov,taxi,bus,delivery,transport,lightrail,cityrail,rail_slow,rail_fast,motorcycle,bicycle,pedestrian --tls.join --junctions.join --junctions.join-dist 25 --tls.join-dist 25 --no-turnarounds --no-internal-links --no-turnarounds.tls -o ".$absolute_path."db/nets/$v.net.xml > /dev/null 2>>".$absolute_path."logs/_errors.log");
		}

		if(!file_exists("./db/data/$v-junctions.data") || !file_exists("./db/data/$v-edges.data")) {
			parse_net($v);
		}

		if(!file_exists("./db/output/$v-$modif-$wlanes-$nbsensors-$propagation.csv")) {
			shell_exec("export SUMO_HOME=".SUMO_HOME." && ".PYTHON." py/launch.py --identifiant $v --modif $modif --propagation $propagation --nbsensors $nbsensors --wlanes $wlanes > ".$absolute_path."logs/$v.log 2>>".$absolute_path."logs/_errors.log");
		}
	
		if(!file_exists("./db/output/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype") && $type != 4) {
			$paparamsup = ($paramsup != "" ? " --paramsup $paramsup" : "");
			shell_exec(PYTHON." ".$absolute_path."py/analysis.py --identifiant $v --modif $modif --propagation $propagation --nbsensors $nbsensors --wlanes $wlanes --analysistype $type$paparamsup 2>>".$absolute_path."logs/_errors.log");
		}

		if($boolhisto) {
			if($type == 4) $filehisto = "./db/output/$v-$ntype";
			else $filehisto = "./db/output/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype";
			$n = file_get_contents($filehisto);
			include ("./db/info/$v.inc.php");
			$method = preg_replace("#\.(osm|net|xml|osm\.xml|net\.xml)$#i", "", $tinfo);
			fputs($pfich, "\"".$method."\" ".$n."\n");
		}
		else {
            if($bool_frequence) {
    			if(!file_exists("./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype-freq.dat")) {
    				$res = Array();
    				$lines = file("./db/output/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype");

    				foreach ($lines as $linevalue)
    				{
    					$n = floatval($linevalue);
    					$res[$n] = ($res[$n] ? $res[$n]+1 : 1);
    				}
    				ksort($res, SORT_NUMERIC);

    				$top = 0;
                    $somme = 0;
    				foreach($res as $edge => $occ) {
                        if($edge > $top)
                            $top = $edge;
                        $somme += $occ;
    				}

                    $interval = 100;

                    $res2 = Array();
                    $dep = 0;
                    for($dep = 0; $dep <= ceil($top/$interval)-1; $dep++) {
                        $total = 0;
                        $total_nombre = 0;
                        foreach($res as $resk => $resv) {
                            if($resk >= $dep * $interval && $resk < (($dep+1) * $interval)) {
                                $total += $resv;
                                $total_nombre++;
                            }
                            if($resk >= ($dep+1) * $interval)
                                break;
                        }
                        $res2[(($dep+1)*$interval)-($interval/2)] = $total/$total_nombre;
                    }

                    unset($res);

    				$pfich = fopen("./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype-freq.dat", 'a');
    				foreach($res2 as $resk => $resv) {
    					$tmp = $resv/$somme;
    					$list = "$resk $tmp";
    					$list .= "\n";
    					
    					fputs($pfich, $list);	
    				}
    				fclose($pfich);
    			}
            }
            else {
    			if(!file_exists("./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype.dat")) {
    				$res = Array();
    				$lines = file("./db/output/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype");
    				foreach ($lines as $linevalue)
    				{
    					$n = sprintf("%.12f",floatval($linevalue));
    					$res[$n] = ($res[$n] ? $res[$n]+1 : 1);
    				}
    				ksort($res, SORT_NUMERIC);
    				$top = 0;
    				foreach($res as $resmv) {
    					$top += $resmv;
    				}
    
    				$pfich = fopen("./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype.dat", 'a');
    				$tot = 0;
    				foreach($res as $resk => $resv) {
    					$tot += $resv;
    					$tmp = $tot/$top;
    					$list = "$resk $tmp";
    					$list .= "\n";
    					
    					fputs($pfich, $list);	
    				}
    				fclose($pfich);
    			}
            }
		}
	}
	if($boolhisto)
		fclose($pfich);
	
	echo "<div style='display:block;'>";
	if($boolhisto) {
		$res = array();
		$lines = file($plot_file_dat.'.dat');
		foreach ($lines as $linevalue)
		{
			$out = array();
			preg_match('#^("[^"]+")[^0-9\.-]+([0-9\.-]+)$#', $linevalue, $out);
			$res[$out[1]] = floatval($out[2]);
		}
		arsort($res, SORT_NUMERIC);

		@unlink($plot_file_dat.'.dat');

		$pfich = fopen($plot_file_dat.'.dat', 'a');
		foreach($res as $resk => $resv) {
			$list = "$resk $resv";
			$list .= "\n";
			fputs($pfich, $list);	
		}
		fclose($pfich);

   		$commande = 'unset xlabel;set ylabel "'.$label.'";unset key;set grid;set autoscale;set ytics auto;set yrange[0:];set size 1,1;set terminal png;set style data histograms;set xtic rotate by 90;set xtic offset -1,0.8;set style fill solid 1.00 border -1;set bmargin 1;set tmargin 1;plot "'.$plot_file_dat.'.dat" using 2:xtic(1) title "Test";set output "'.$plot_file_png.'.png";replot;set terminal postscript eps color enhanced "Helvetica" 20;set size '.(count($osmlist) > 25 ? "2.5,1" : "1,1").';set output "'.$plot_file_png.'.eps";replot';
        shell_exec(GNUPLOT." -e '$commande'");
	}
	else {
        if($bool_frequence) {
    		foreach($osmlist as $v) {
                if(!file_exists('./db/plots/'.$v.'-'.$modif.'-'.$wlanes.'-'.$nbsensors.'-'.$propagation.'-'.$ntype.'-freq.png')) {
                    $lines = file("./db/output/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype");

/*

                    $nombre_valeurs = 0;
                    $somme_valeur = 0;

                    $nombre_valeurs_log = 0;
                    $somme_valeur_log = 0;

            		foreach ($lines as $linevalue)
            		{
                        // Norm
                        $valeur = floatval($linevalue);
                        $somme_valeur += $valeur;
                        $nombre_valeurs += 1;

                        // Log
                        if($valeur > 0) {
                            $nombre_valeurs_log++;
                            $somme_valeur_log += log($valeur);
                        }
                    }

                    $moyenne = $somme_valeur/$nombre_valeurs;
                    $moyennelog = $somme_valeur_log/$nombre_valeurs_log;

                    $variance = 0;
                    $variance_nombre = 0;

                    $variance_log = 0;

            		foreach ($lines as $linevalue)
            		{
                        $valeur = floatval($linevalue);
                        $variance += pow(($valeur- $moyenne), 2);
                        if($valeur > 0)
                            $variance_log += pow((log($valeur) - $moyennelog), 2);
                    }  

                    $ecart_type = sqrt($variance/$nombre_valeurs);
                    $log_ecart_type = round(sqrt($variance_log/$nombre_valeurs_log), 2);
                    $lambda = round(1./$moyenne, 2);

                    $gamma_s = log($moyenne) - $moyennelog;
                    $gamma_shape = (3 - $gamma_s + sqrt(pow(s - 3,2) + 24*$gamma_s))/(12 * $gamma_s);
                    $gamma_scale = $moyenne*(1/$gamma_shape);

                    if (!function_exists('distribution')) {
                        function distribution($quoi, $x) {
                            global $ecart_type, $moyenne, $lambda, $moyennelog, $log_ecart_type;

                            // Normal distribution
                            if($quoi == 1) 
                                return (1.0/($ecart_type*sqrt(2*pi()))) * exp(-pow($x-$moyenne,2) / (2*pow($ecart_type,2)));
    
                            // Exponential distribution
                            if ($quoi == 2) 
                                return $lambda*exp(-1*$lambda*$x);

                            // Log-normal distribution
                            if ($quoi == 3) 
                                return (1.0/($x*$log_ecart_type*sqrt(2*pi()))) * exp(-pow(log($x)-$moyennelog,2) / (2*pow($log_ecart_type,2)));

                            // Gamma distribution
                            if ($quoi == 4) 
                                return 0;
                        }
                    }

                    $d = array();
                    $daff = array();
                    $d[0] = '';
                    $daff[0] = '';
                    $d[1] = 'd1(x)=(1.0/('.$ecart_type.'*sqrt(2*pi))) * exp(-(x-'.$moyenne.')**2 / (2*'.$ecart_type.'**2));';
                    $daff[1] = ', d1(x) lc rgb "#000000" lt 2 lw 3 title "Normal distribution (u='.$moyenne.', a='.$ecart_type.')"';
                    $d[2] = 'd2(x)='.$lambda.'*exp(-1*'.$lambda.'*x);';
                    $daff[2] = ', d2(x) lc rgb "#000000" lt 2 lw 3 title "Exponential distribution (lambda='.$lambda.')"';
                    $d[3] = 'd3(x)=(1./(x*'.$log_ecart_type.'*sqrt(2*pi)))*exp(-1*((log(x)-'.$moyennelog.')**2)/(2*'.$log_ecart_type.'**2));';
                    $daff[3] = ', d3(x) lc rgb "#000000" lt 2 lw 3 title "Log-normal distribution (u='.$moyennelog.', a='.$log_ecart_type.')"';
                    $d[4] = '_ln_dgamma(x, a, b) = a*log(b) - lgamma(a) + (a-1)*log(x) - b*x;dgamma(x, shape, rate) = (x<0)? 0 : (x==0)? ((shape<1)? 1/0 : (shape==1)? rate : 0) : (rate==0)? 0 : exp(_ln_dgamma(x, shape, rate));pgamma(x, shape, rate) = (x<0)? 0 : igamma(shape, x*rate);d4(x, k, t) = dgamma(x, k, 1.0/t);';
                    $daff[4] = ', d4(x,'.$gamma_shape.','.$gamma_scale.') lc rgb "#000000" lt 2 lw 3 title "Gamma distribution (k='.$gamma_shape.', t='.$gamma_scale.')"';

                    $fonction_sup = "";

                    unset($lines);
                    $lines = file("./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype-freq.dat");

                    $fcti = 1;
                    $fctmax = 3;
                    $fctselected = 0;
                    $difselected = 9999999999;
                    for($i = $fcti; $i <= $fctmax; $i++) {
                        $avgdiff = 0;
                        $totdiff = 0;
                       	foreach ($lines as $linevalue)
                		{
                            $valte = explode(" ", $linevalue);
                            if($i != 3 || (floatval($valte[0]) > 0 && $i == 3)) {
                                $avgdiff += abs($valte[1] - distribution($i, $valte[0]));
                                $totdiff++;
                            }
                        }
                        $avgd = $avgdiff/$totdiff;

                        if($avgd < $difselected) {
                            $difselected = $avgd;
                            $fctselected = $i;
                        }
                    }

                    $fctselected = 0;
*/

                    //$commande = $d[$fctselected];
        		    $commande = 'set xlabel "'.$labelx.'";set ylabel "frequence";set autoscale;unset key;set ytics auto; set xtics auto;set yrange[0:];set size 1,1;set terminal png;set style fill solid;plot "./db/plots/'.$v.'-'.$modif.'-'.$wlanes.'-'.$nbsensors.'-'.$propagation.'-'.$ntype.'-freq.dat" using 1:2 w boxes title "distribution"';
                    //$commande .= $daff[$fctselected];
                    $commande .= ';set output "./db/plots/'.$v.'-'.$modif.'-'.$wlanes.'-'.$nbsensors.'-'.$propagation.'-'.$ntype.'-freq.png";replot;set terminal postscript eps color enhanced "Helvetica" 20;set size 1,1;set output "./db/plots/'.$v.'-'.$modif.'-'.$wlanes.'-'.$nbsensors.'-'.$propagation.'-'.$ntype.'-freq.eps";replot';

                    shell_exec(GNUPLOT." -e '$commande' 2>> ./logs/_gnuplot_error");
                }
            }
        }
        else {
    		$pos = "center";	
    		if($logscale!="")
    			$logscale = ";set logscale $logscale";
    	
    		$plot = "";
    		$i = 0;
    		foreach($osmlist as $v) {
    			if($i > 0) $plot .= ',';	
    			include ("./db/info/$v.inc.php");
    			$method = preg_replace("#\.(osm|net|xml|osm\.xml|net\.xml)$#i", "", $tinfo);
    			$plot .= '"./db/plots/'.$v.'-'.$modif.'-'.$wlanes.'-'.$nbsensors.'-'.$propagation.'-'.$ntype.'.dat" using 1:2 with linespoints lw 4 ps 1.5 title "'.$method.'"';
    			$i++;
    		}
    		$labely = "%";
    
    		$commande = 'set xlabel "'.$labelx.'";set ylabel "'.$labely.'";set autoscale'.$logscale.';set ytics auto;set xtics auto;set grid;set size 1,1;set terminal png;set key on inside right '.$pos.';set style fill solid 1.00 border -1;plot '.$plot.';set output "'.$plot_file_png.'.png";replot;set terminal postscript eps color enhanced "Helvetica" 20;set output "'.$plot_file_png.'.eps";replot';
            shell_exec(GNUPLOT." -e '$commande'");
        }
	}
	echo "</div>";
}
if($boolhisto) {
    echo "<img src='".$plot_file_png.".png'/>";
    echo "<br/><br/>";
	echo "<hr/><a href='".$plot_file_png.".eps' target='_blank'>Download the EPS file</a> - <a href='".$plot_file_dat.".dat' target='_blank'>Download the GNUPLOT file datas</a> - <a href='./raw.php?t=1&f=".$plot_file_dat.".dat' target='_blank'>Download raw datas</a><hr/>";
}
else if($bool_frequence) {

    foreach($osmlist as $v) {
        include ("./db/info/$v.inc.php");
        echo "<center><b>$tinfo</b></center><br/>";
        echo "<img src='./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype-freq.png'/>";
        echo "<br/><br/>";
    	echo "<hr/><a href='./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype-freq.eps' target='_blank'>Download the EPS file</a> - Download the GNUPLOT file data: ";
	    echo "<a href='./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype-freq.dat' target='_blank'>$v</a>";
        echo "<br/><br/><hr/><br/><br/>";
	}
}
else {
    echo "<img src='".$plot_file_png.".png'/>";
    echo "<br/><br/>";
	echo "<hr/><a href='".$plot_file_png.".eps' target='_blank'>Download the EPS file</a> - Download the GNUPLOT files data: ";
		$i = 0;
		foreach($osmlist as $v) {
			if($i != 0) echo ", ";
			echo "<a href='./db/plots/$v-$modif-$wlanes-$nbsensors-$propagation-$ntype.dat' target='_blank'>$v</a>";
			$i++;
		}
	echo "<hr/>";
}
?>


