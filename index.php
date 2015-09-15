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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Characterizing the Topology of an Urban Wireless Sensor Network for Road Traffic Management</title>
	<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.css" />
	<script src="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.js"></script>
    <link href="./ressources/general.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-6194817-8']);
	  _gaq.push(['_trackPageview']);
	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	
	</script>
</head>
<body>
	<div id="titre">
		<h1><a href="./">Characterizing the Topology of an Urban Wireless Sensor Network<br/> for Road Traffic Management</a></h1>
	</div>
	
	<div id='liens'>
		&copy; <a href='http://www.sfaye.com/'>S&eacute;bastien Faye</a>, 2014 (v<?php echo VERSION; ?>) &nbsp;&nbsp;|&nbsp;&nbsp;<a href='https://github.com/sfaye/TopologyWSN/'>GitHub</a>, <a href='https://github.com/sfaye/TopologyWSN/archive/master.zip'>Download source</a>, <a href='#' onclick='alert("This file contains our main results. Without it, you can see the results on this website, or generate them yourself with the project source. To use it you have to copy the folders into ./db/."); document.location.href="./_downloads/database.zip"'>Download database (~4.2GB)</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href='./LICENCE'>Licence</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</div>
	
	<img src='./ressources/images/loader.gif' id='loader'/>

	<div id="cadre">

		<form id="upload" enctype="multipart/form-data">
			<fieldset<?php echo (!ALLOW_UPLOADING ? " style='filter: alpha(opacity=60); -moz-pacity: 0.60; opacity: 0.60;'" : ""); ?>>
   				<legend>Upload a new map</legend>
                <?php
                if(!ALLOW_UPLOADING) {
                    echo '
                <div style="text-align: center; color: darkred; font-weight: bold; font-size: 16px;">disabled<input type="hidden" name="fileselect" id="fileselect"/><input type="hidden" name="filedrag" id="filedrag"/></div>';
                }
                else {
                    echo '
				<div>
					<label for="fileselect">File to upload:</label>
					<input type="file" id="fileselect" name="fileselect[]" multiple="multiple" />
					<div id="filedrag">or drop here</div>
				</div>';
                }
                ?>
			</fieldset>
	
			<fieldset>
				<legend>Database</legend>
				<div style='margin-left: auto; margin-right: auto; width: 940px;'>
				<div id="map"></div>

				<?php
				$js_leafletjs = "";
				echo "<div class='tab bold top'>ID</div><div class='tab big bold top'>File name</div><div class='tab big bold top'>Date</div><div class='tab bold center top'>Results</div><div class='tab bold center top'>Current tasks</div><div class='tab bold center top'>Compare</div>";
				$i = 0;
				$data = Array();
				$handle=@opendir("./db/info/");
				while($tmp = readdir($handle))
				{
					if(preg_match("#.inc.php#i", $tmp)) 
					{
						include("./db/info/".$tmp);
						$data[$tmp] = $tinfo;
					}
				}
				closedir($handle);
				asort($data);

				foreach($data as $tmp => $tinfo) {
					$col = ($i%2==0 ? '' : ' col');
					$tid = str_replace(".inc.php", "", $tmp);

					$nbtasks = 0;
					$handle_nbtasks=@opendir("./db/locks/");
					while($tmp_nbtasks = readdir($handle_nbtasks))
					{
						if(preg_match("#".$tid."#i", $tmp_nbtasks)) 
						{
							$nbtasks++;
						}
					}
					closedir($handle);

					if(!file_exists("./db/coords/$tid.inc.php")) {
						$ouverture = @fopen("./db/uploads/$tid", "r");
						$premier = fread($ouverture, 500);
						fclose($ouverture);

						$minlat = array();
						preg_match_all('#<bounds.+minlat="([0-9\.-]+)"[^>]+>#isU', $premier, $minlat);
						$minlat = floatval($minlat[1][0]);

						$minlon = array();
						preg_match_all('#<bounds.+minlon="([0-9\.-]+)"[^>]+>#isU', $premier, $minlon);
						$minlon = floatval($minlon[1][0]);

						$maxlat = array();
						preg_match_all('#<bounds.+maxlat="([0-9\.-]+)"[^>]+>#isU', $premier, $maxlat);
						$maxlat = floatval($maxlat[1][0]);

						$maxlon = array();
						preg_match_all('#<bounds.+maxlon="([0-9\.-]+)"[^>]+>#isU', $premier, $maxlon);
						$maxlon = floatval($maxlon[1][0]);

						$pointeur = fopen("./db/coords/$tid.inc.php", 'a+');
						fputs($pointeur, '<?php $lat = "'.(($minlat+$maxlat)/2).'"; $lon = "'.(($minlon+$maxlon)/2).'"; ?>');
						fclose($pointeur);
					}

					include "./db/coords/$tid.inc.php";

					if($lat != 0 || $lon != 0)
						$js_leafletjs .= "L.marker([$lat, $lon], {icon: smallIcon}).addTo(map).bindPopup('$tinfo <a onclick=\"go_parameters($tid, 0);\">[see]</a>');";

					echo "<div class='tab first$col'>$tid</div><div class='tab big$col'>$tinfo</div><div class='tab big$col'>".date('Y/m/d, H:i', $tid)."</div><div class='tab center$col'><a onclick='go_parameters($tid, 0);'>&rarr; see</a></div><div class='tab center$col'>$nbtasks</div><div class='tab center$col'><input type='checkbox' name='check[]' onclick='verif_check()' value='$tid'/></div><br/>";
					$i++;
				}
				if($i == 0) echo "There are no existing network!";
				?>
				<br/><input type='button' value='plot' id='plot_button' onclick='plot()'/> <input type='button' value='select all' id='select_all_button' onclick='select_all()'/>
				</div>
			</fieldset>
		</form>

		<form id="launch">
			<h1>Parameters</h1>
			<fieldset>
				<legend>Communication range</legend>
				<div id="com_range_choices">
					<input type='radio' name='com_range[]' value='1' onclick='verif_com_range_choice()'/> Manual<br/>
					<input type='radio' name='com_range[]'  value='2' onclick='verif_com_range_choice()' checked/> Auto
				</div>
				<div id='com_range_choice1'><input type="text" name="propagation" id="propagation" value="200" onchange="verif_launch_button(0);" /> meters<br/><br/><input type='button' value='infinite range' onclick='$id("propagation").value="infinite";verif_launch_button(0);'/></div>
				<div id='com_range_choice2'>based on path loss model: <select name="modele_propagation" id="modele_propagation"><option value="-1">free space</option><option value="-2" selected="selected">IEEE 802.15.2-2003</option></select></div>
			</fieldset>
			<fieldset>
				<legend>Intersections to control</legend>
				<div>
					<select name="wlanes" id="wlanes" size="2" onchange="verif_launch_button(0);"><option value="1" selected="selected">(1) all the intersections</option><option value="0">(0) only the traffic light intersections</option></select>
				</div>
			</fieldset>
			<fieldset>
				<legend>Deployment</legend>
				<div style='margin-bottom: 10px;'>
					<select name="nbsensors" id="nbsensors" size="4" onchange="verif_launch_button(0);"><option value="1">(1) 1 sensor / intersection</option><option value="4">(1) 1 sensor / road (at the exit)</option><option value="2" selected="selected">(2) 1 sensor / lane (at the exit)</option><option value="3">(3) 2 sensors / lane (at the entrance, at the exit)</option></select>
				</div>
				<div style='margin-bottom: 5px;'>
					<select name="modif" id="modif" size="3" onchange="verif_launch_button(0); verif_modif();"><option value="1" selected="selected">(1) do not change the network</option><option value="2">(2) remove all articulation points</option><option value="3">(3) merge adjacent components</option></select>
				</div>
				<div id='modif_3_div'>
					distance: <input type="text" name="modif_3" id="modif_3" value="2" style="width: 20px;" onchange="verif_modif();" /> sensor(s)
				</div>
			</fieldset>
			<div id="background"><input type="checkbox" name="launch_background" id="launch_background"/> run in background?</div>
			<input type="button" onclick="document.location.href='./';" value="back" id="back_button" class="red"/> <input type="button" id="launch_button" onclick="launch();" value="generate" class="green"/>
		</form>

		<div id="upload_result">
			<div id="message"></div>

			<fieldset>
				<legend align="right" class="big_size">Map informations</legend>
				<div>
					Internal ID: <i><span id="launch_id_map"></span></i>.
				</div>
			</fieldset>
			<fieldset>
				<legend align="right" class="big_size">Simulations</legend>
				<div id="available_results">

				</div>
			</fieldset>
		</div>
		
		<div id="result">
			<fieldset>
				<legend>Download</legend>
				<div style="tab bold">Graph (CSV, <i>source;destination;weight</i>): <span id="csv_link"></span></div>
				<div style="tab bold">Graph (CSV, <i>source;destination</i>): <span id="csv2_link"></span></div>
				<div style="tab bold">Node positions: <span id="np_link"></span></div>
				<div style="tab bold">SUMO net file: <span id="sumo_link"></span></div>
				<div style="tab bold">OMNeT++ example: <span id="omnetpp_link"></span></div>
			</fieldset>
			<fieldset style="margin-bottom: 80px; text-align: center; min-height: 400px;">
				<legend>Graph analysis</legend>
				What would you like to see? &nbsp;&nbsp;&nbsp;&nbsp;
				<select id="select_graph_analysis" onchange="get_analysis();">
					<option value="" class="lv1">infrastructure</option>
						<option value='1' selected="selected">---- (view) nodes</option>
						<option value='2'>---- (size) nodes</option>
						<option value='3'>---- (size) nodes per square kilometer</option>
						<option value='4'>---- (size) network area</option>
					<option value="" class="lv1">network graph</option>
						<option value='5'>---- (view) cliques</option>
						<option value='6'>---- (size) cliques</option>
						<option value='7'>---- (size) max clique</option>
						<option value='8'>---- (view) k-cliques</option>
						<option value='9'>---- (size) k-cliques</option>
						<option value='11'>---- (view) connected components</option>
						<option value='300'>---- (3d view) connected components</option>
						<option value='12'>---- (size) connected components</option>
						<option value='42'>---- (size) connected components with one node</option>
						<option value='35'>---- (size) biconnected components</option>
						<option value='40'>---- (size) connected components / total nodes</option>
						<option value='41'>---- (size) biconnected components / total nodes</option>
						<option value='38'>---- (size) connected components per square kilometer</option>
						<option value='39'>---- (size) biconnected components per square kilometer</option>
						<option value='13'>---- (size) average connected component diameter (hops)</option>
						<option value='14'>---- (size) average connected component diameter (meters)</option>
						<option value='46'>---- (size) average connected component diameter (weights)</option>
						<option value='16'>---- (size) average connected component articulation points</option>
						<option value='36'>---- (size) average biconnected component diameter (hops)</option>
						<option value='37'>---- (size) average biconnected component diameter (meters)</option>
						<option value='17'>---- (size) average degree</option>
						<option value='18'>---- (size) average clustering coefficient</option>
						<option value='19'>---- (size) average betweeness centrality</option>
						<option value='20'>---- (size) average pagerank</option>
					<option value="" class="lv1">max connected component graph</option>
						<option value='44'>---- (view) nodes</option>
						<option value='21'>---- (size) nodes</option>
						<option value='22'>---- (size) nodes / total nodes</option>
						<option value='23'>---- (size) cliques</option>
						<option value='24'>---- (size) max clique</option>
						<option value='25'>---- (size) k-cliques</option>
						<option value='27'>---- (size) diameter (hops)</option>
						<option value='28'>---- (size) diameter (meters)</option>
						<option value='47'>---- (size) diameter (weights)</option>
						<option value='30'>---- (size) articulation points</option>
						<option value='31'>---- (size) average degree</option>
						<option value='32'>---- (size) average clustering coefficient</option>
						<option value='33'>---- (size) average betweeness centrality</option>
						<option value='43'>---- (size) average closeness centrality</option>
						<option value='34'>---- (size) average pagerank</option>
					<option value="" class="lv1">largest connected component graph</option>
						<option value='45'>---- is the max connected component graph ?</option>
				</select>&nbsp;&nbsp;<a onclick="get_analysis();"><img src='./ressources/images/refresh.png' style='border: 0px; width: 16px; height: 16px;'/></a><br/><br/>
				<div id='graph_analysis'></div>
			</fieldset>

			<select id="select_graph_analysis_inter" style="display: none;">
				<option value="" class="lv1">infrastructure</option>
					<option value='2' selected="selected">---- (size) nodes</option>
					<option value='3'>---- (size) nodes per square kilometer</option>
					<option value='4'>---- (size) network area</option>
			</select>
		</div>

		<div id="plot">
			<fieldset style="margin-bottom: 80px; text-align: center; min-height: 400px;">
				<legend>Plot <select id="select_plot_type" onchange="set_plot_type()"><option value='1'>histograms, distributions</option><option value='2'>stats versus network modifications</option></select></legend>
				What would you like to see?&nbsp;&nbsp;<a onclick="get_plot();" id="go_plot"><img src='./ressources/images/refresh.png' style='border: 0px; width: 16px; height: 16px;'/></a><br/>
				<select id="select_plot" onchange="get_plot();">
					<option value="" class="lv1">cdf</option>
						<option value="" class="lv2">---- network graph</option>
							<option value='110'>-------- edges length</option>
							<option value='102'>-------- degree distribution</option>
							<option value='100'>-------- cliques size distribution</option>
							<option value='101'>-------- connected components size distribution</option>
							<option value='120'>-------- connected components inter-distance distribution</option>
							<option value='121'>-------- mininum inter-distance between nearest connected components distribution</option>
							<option value='150'>-------- Connected components neighborhood inter-distance distribution (based on Delaunay triangulation)</option>
							<option value='103'>-------- connected components diameter (hops) distribution</option>
							<option value='104'>-------- connected components diameter (meters) distribution</option>
							<option value='115'>-------- connected components diameter (weights) distribution</option>
							<option value='105'>-------- connected components articulation points distribution</option>
							<option value='112'>-------- articulation points distribution (% / components nodes)</option>
							<option value='113'>-------- betweeness centrality distribution</option>
							<option value='117'>-------- closeness centrality distribution</option>
							<option value='122'>-------- clustering coefficient distribution</option>
						<option value="" class="lv2">---- max connected component graph</option>
							<option value='111'>-------- edges length</option>
							<option value='107'>-------- degree distribution</option>
							<option value='106'>-------- cliques size distribution</option>
							<option value='108'>-------- inter-distance (hops) distribution</option>
							<option value='109'>-------- inter-distance (meters) distribution</option>
							<option value='116'>-------- inter-distance (weights) distribution</option>
							<option value='114'>-------- betweeness centrality distribution</option>
							<option value='118'>-------- closeness centrality distribution</option>
							<option value='123'>-------- clustering coefficient distribution</option>
					<option value="" class="lv1">frequence</option>
						<option value="" class="lv2">---- network graph</option>
							<option value='210'>-------- edges length</option>
							<option value='202'>-------- degree distribution</option>
							<option value='200'>-------- cliques size distribution</option>
							<option value='201'>-------- connected components size distribution</option>
							<option value='220'>-------- connected components inter-distance distribution</option>
							<option value='221'>-------- mininum inter-distance between nearest connected components distribution</option>
							<option value='250'>-------- Connected components neighborhood inter-distance distribution (based on Delaunay triangulation)</option>
							<option value='203'>-------- connected components diameter (hops) distribution</option>
							<option value='204'>-------- connected components diameter (meters) distribution</option>
							<option value='215'>-------- connected components diameter (weights) distribution</option>
							<option value='205'>-------- connected components articulation points distribution</option>
							<option value='212'>-------- articulation points distribution (% / components nodes)</option>
							<option value='213'>-------- betweeness centrality distribution</option>
							<option value='217'>-------- closeness centrality distribution</option>
							<option value='222'>-------- clustering coefficient distribution</option>
						<option value="" class="lv2">---- max connected component graph</option>
							<option value='211'>-------- edges length</option>
							<option value='207'>-------- degree distribution</option>
							<option value='206'>-------- cliques size distribution</option>
							<option value='208'>-------- inter-distance (hops) distribution</option>
							<option value='209'>-------- inter-distance (meters) distribution</option>
							<option value='216'>-------- inter-distance (weights) distribution</option>
							<option value='214'>-------- betweeness centrality distribution</option>
							<option value='218'>-------- closeness centrality distribution</option>
							<option value='223'>-------- clustering coefficient distribution</option>
					<option value="" class="lv1">histogram</option>
						<option value="" class="lv2">---- infrastructure</option>
							<option value='2' selected="selected">-------- nodes</option>
							<option value='3'>-------- nodes per square kilometer</option>
							<option value='4'>-------- network areas</option>
						<option value="" class="lv2">---- network graph</option>
							<option value='6'>-------- cliques</option>
							<option value='7'>-------- max clique size</option>
							<option value='9'>-------- k-cliques</option>
							<option value='12'>-------- connected components</option>
						    <option value='42'>-------- connected components with one node</option>
							<option value='35'>-------- biconnected components</option>
						    <option value='40'>-------- connected components / total nodes</option>
						    <option value='41'>-------- biconnected components / total nodes</option>
							<option value='38'>-------- connected components per square kilometer</option>
							<option value='39'>-------- biconnected components per square kilometer</option>
							<option value='13'>-------- average connected component diameter (hops)</option>
							<option value='14'>-------- average connected component diameter (meters)</option>
						    <option value='46'>-------- average connected component diameter (weights)</option>
							<option value='16'>-------- average connected component articulation points</option>
							<option value='36'>-------- average biconnected component diameter (hops)</option>
							<option value='37'>-------- average biconnected component diameter (meters)</option>
							<option value='17'>-------- average degree</option>
							<option value='18'>-------- average clustering coefficient</option>
							<option value='19'>-------- average betweeness centrality</option>
							<option value='20'>-------- average pagerank</option>
						<option value="" class="lv2">---- max connected component graph</option>
							<option value='21'>-------- nodes</option>
							<option value='22'>-------- nodes / total nodes</option>
							<option value='23'>-------- cliques</option>
							<option value='24'>-------- max clique</option>
							<option value='25'>-------- k-cliques</option>
							<option value='27'>-------- diameter (hops)</option>
							<option value='28'>-------- diameter (meters)</option>
						    <option value='47'>-------- diameter (weights)</option>
							<option value='30'>-------- articulation points</option>
							<option value='31'>-------- average degree</option>
							<option value='32'>-------- average clustering coefficient</option>
							<option value='33'>-------- average betweeness centrality</option>
						    <option value='43'>-------- average closeness centrality</option>
							<option value='34'>-------- average pagerank</option>
				</select>
    			<select id="select_plot_inter" style="display: none;">
					<option value="" class="lv1">cdf</option>
							<option value='110'>---- edges length</option>
					<option value="" class="lv1">frequence</option>
							<option value='210'>---- edges length</option>
					<option value="" class="lv1">histogram</option>
							<option value='2' selected="selected">---- nodes</option>
							<option value='3'>---- nodes per square kilometer</option>
							<option value='4'>---- network areas</option>
    			</select>
				<select id="select_plot_2" onchange="get_plot_2();">
						<option value="" class="lv2">---- infrastructure</option>
							<option value='2' selected="selected">-------- nodes</option>
							<option value='3'>-------- nodes per square kilometer</option>
						<option value="" class="lv2">---- network graph</option>
							<option value='6'>-------- cliques</option>
							<option value='7'>-------- max clique size</option>
							<option value='9'>-------- k-cliques</option>
							<option value='12'>-------- connected components</option>
						    <option value='42'>-------- connected components with one node</option>
							<option value='35'>-------- biconnected components</option>
						    <option value='40'>-------- connected components / total nodes</option>
						    <option value='41'>-------- biconnected components / total nodes</option>
							<option value='38'>-------- connected components per square kilometer</option>
							<option value='39'>-------- biconnected components per square kilometer</option>
							<option value='13'>-------- average connected component diameter (hops)</option>
							<option value='14'>-------- average connected component diameter (meters)</option>
						    <option value='46'>-------- average connected component diameter (weights)</option>
							<option value='16'>-------- average connected component articulation points</option>
							<option value='36'>-------- average biconnected component diameter (hops)</option>
							<option value='37'>-------- average biconnected component diameter (meters)</option>
							<option value='17'>-------- average degree</option>
							<option value='18'>-------- average clustering coefficient</option>
							<option value='19'>-------- average betweeness centrality</option>
							<option value='20'>-------- average pagerank</option>
						<option value="" class="lv2">---- max connected component graph</option>
							<option value='21'>-------- nodes</option>
							<option value='22'>-------- nodes / total nodes</option>
							<option value='23'>-------- cliques</option>
							<option value='24'>-------- max clique</option>
							<option value='25'>-------- k-cliques</option>
							<option value='27'>-------- diameter (hops)</option>
							<option value='28'>-------- diameter (meters)</option>
						    <option value='47'>-------- diameter (weights)</option>
							<option value='30'>-------- articulation points</option>
							<option value='31'>-------- average degree</option>
							<option value='32'>-------- average clustering coefficient</option>
							<option value='33'>-------- average betweeness centrality</option>
						    <option value='43'>-------- average closeness centrality</option>
							<option value='34'>-------- average pagerank</option>
				</select><br/><br/>
				<div id='plot_display'></div>
			</fieldset>
		</div>
		
		<div id="progress"></div>
		
		<div id="dialog">
			<fieldset>
				<legend>Console</legend>
				<div id="dialog_txt"></div>
			</fieldset>
		</div>
	</div>
    <script src='./ressources/general.js?v=<?php echo VERSION; ?>' type='text/javascript'></script>
    <script type='text/javascript'>
    	<?php echo $js_leafletjs; ?>
    </script>
</body>
</html>