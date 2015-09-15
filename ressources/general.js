
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

var id_map = 0;
var nbsensors = 0;
var propagation = 0;
var wlanes = 0;
var lbackground = 0;
var alr_call = 0;
var list_id_map = "";
var choice_com = 0;
var modif = 0;
var plot_type = 1;

function $id(id) {
	return document.getElementById(id);
}

function set_plot_type() {
	plot_type = $id('select_plot_type').value;
	if(plot_type == '2') {
		$id('go_plot').onclick = function() { get_plot_2(); };
		$id('select_plot').style.display = "none";
		$id('select_plot_2').style.display = "block";
		get_plot_2();
	}
	else {
		$id('go_plot').onclick = function() { get_plot(); };
		$id('select_plot').style.display = "block";
		$id('select_plot_2').style.display = "none";
		get_plot();
	}
}

function verif_check() {
	var checks = document.getElementsByName('check[]');
	list_id_map = "";
	var nb_checks = 0;
	for (var i = 0; i < checks.length; i++) {
		if(checks[i].checked) {
			if(nb_checks > 0)
				list_id_map += ","
			list_id_map += checks[i].value;
			nb_checks++;
		}
	}
	if(nb_checks > 0) 
		$id("plot_button").style.display = "block";
	else
		$id("plot_button").style.display = "none";
}

function select_all() {
	var checks = document.getElementsByName('check[]');
	for (var i = 0; i < checks.length; i++) {
		checks[i].checked = true;
	}
	verif_check()
}

function verif_com_range_choice() {
	var com_range = document.getElementsByName('com_range[]');
	if(com_range[0].checked) {
		$id('com_range_choice1').style.display='block';
		$id('com_range_choice2').style.display='none';
		choice_com = 0;
	}
	else if(com_range[1].checked) {
		$id('com_range_choice1').style.display='none';
		$id('com_range_choice2').style.display='block';
		choice_com = 1;
	}
    get_propagation();
}

function verif_modif() {
	if($id('modif').value == '3') {
		$id('modif_3_div').style.display = "block";
		modif = $id('modif_3').value;
	}
	else if($id('modif').value == '2') {
		$id('modif_3_div').style.display = "none";
		modif = -1;
	}
	else {
		$id('modif_3_div').style.display = "none";
		modif = 0;
	}
}

function plot() {
	$id('upload').style.display = 'none';
	$id('plot').style.display = 'block';
	$id('launch').style.display = 'block';
	$id("back_button").disabled = false;
	$id("back_button").onclick = function() {document.location.href="./"};
	$id("launch_button").style.display = "none";
	$id("background").style.display = "none";
	$id("nbsensors").onchange = function() {};
	$id("propagation").onchange = function() {};
	$id("wlanes").onchange = function() {};
	verif_com_range_choice();
	verif_modif();

	get_plot();
}

function FileDragHover(e) {
	e.stopPropagation();
	e.preventDefault();
	e.target.className = (e.type == "dragover" ? "hover" : "");
}

function FileSelectHandler(e) {
	FileDragHover(e);

	$id('upload').style.display = 'none';

	var files = e.target.files || e.dataTransfer.files;

	for (var i = 0, f; f = files[i]; i++) {
		UploadFile(f);
	}
}

function UploadFile(file) {
	var xhr = new XMLHttpRequest();
	if (xhr.upload) {
		$id("loader").style.display = "block";

		var o = $id("progress");
		var progress = o.appendChild(document.createElement("p"));
		progress.appendChild(document.createTextNode("upload " + file.name));

		xhr.upload.addEventListener("progress", function(e) {
			var pc = parseInt(100 - (e.loaded / e.total * 100));
			progress.style.backgroundPosition = pc + "% 0";
		}, false);

		xhr.onreadystatechange = function(e) {
			if (xhr.readyState == 4) {
				progress.className = (xhr.status == 200 ? "success" : "failure");
				if(xhr.status == 200) {
					id_map = xhr.responseText;
					if(file.name.length == file.name.replace(".net","").length)
						osm2sumo();
					else {
						$id("loader").style.display = "none";
                        $id("progress").innerHTML = "";
            		        $id("launch_id_map").innerHTML = id_map;
            		        $id("launch").style.display = "block";
            		        $id("upload_result").style.display = "block";
						$id("back_button").onclick = function() {document.location.href="./"};
						get_available_results();
						verif_launch_button(1);
						verif_modif();
						alr_call = 1;
					}
				}
			}
		};

		xhr.open("POST", "upload.php", true);
		xhr.setRequestHeader("X_FILENAME", file.name);
		xhr.send(file);
	}
}

function osm2sumo() {
	var o = $id("progress");
	var progress = o.appendChild(document.createElement("p"));
	progress.appendChild(document.createTextNode("convert osm -> sumo format"));

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4) {
		   progress.className = (xhr.status == 200 ? "success" : "failure");
                if(xhr.status == 200) {
				$id("loader").style.display = "none";
				$id("progress").innerHTML = "";
				$id("launch_id_map").innerHTML = id_map;
				$id("launch").style.display = "block";
				$id("upload_result").style.display = "block";
				$id("back_button").onclick = function() {document.location.href="./"};
				get_available_results();
				verif_launch_button(1);
				verif_modif();
                verif_com_range_choice();
				alr_call = 1;
			}
		}
	};

	xhr.open("POST", "osm2sumo.php?osm=" + id_map, true);
	xhr.send(null);
}

function get_available_results() {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4) {
		   progress.className = (xhr.status == 200 ? "success" : "failure");
                if(xhr.status == 200) {
				$id("available_results").innerHTML = xhr.responseText;	
			}
		}
	};

	xhr.open("POST", "get_available_results.php?osm=" + id_map, true);
	xhr.send(null);
}

var prev_ret = "";
function get_propagation() {
    var ret = "";
    var com_range = document.getElementsByName('com_range[]');
    if($id("propagation").value == "infinite" && com_range[0].checked) {
        ret = "10000000";
        if(prev_ret == "") {
            var tmp_txt = $id('select_graph_analysis').innerHTML;
            $id('select_graph_analysis').innerHTML = $id('select_graph_analysis_inter').innerHTML;
            $id('select_graph_analysis_inter').innerHTML = tmp_txt;

            var tmp_txtb = $id('select_plot').innerHTML;
            $id('select_plot').innerHTML = $id('select_plot_inter').innerHTML;
            $id('select_plot_inter').innerHTML = tmp_txtb;
        }
        prev_ret = "inf";
    }
    else {
        ret = $id("propagation").value;
        if(prev_ret == "inf") {
            var tmp_txt = $id('select_graph_analysis').innerHTML;
            $id('select_graph_analysis').innerHTML = $id('select_graph_analysis_inter').innerHTML;
            $id('select_graph_analysis_inter').innerHTML = tmp_txt;

            var tmp_txtb = $id('select_plot').innerHTML;
            $id('select_plot').innerHTML = $id('select_plot_inter').innerHTML;
            $id('select_plot_inter').innerHTML = tmp_txtb;
        }
        prev_ret = "";
    }
    return ret;
}

function go_parameters(id, message) {
	id_map = id;
	$id("loader").style.display = "none";
	$id("progress").innerHTML = "";
	boucle = -1;
	$id("dialog").style.display = "none";
    $id("back_button").disabled = false;
	$id("launch_button").disabled = false;
	$id("launch_id_map").innerHTML = id_map;
	$id("launch").style.display = "block";
	$id("upload_result").style.display = "block";
	$id('upload').style.display = 'none';
	$id('result').style.display = 'none';
	$id("back_button").onclick = function() {document.location.href="./"};
	verif_com_range_choice();

	get_available_results();
	if(alr_call == 0) {
		verif_launch_button(1);
		verif_modif();
		alr_call = 1;
	}
	if(message == 1) {
		$id("message").innerHTML = "<div class='message_success'>Success</div>";
		setTimeout(function() {$id("message").innerHTML = ""}, 5000);
	}
}

var boucle2 = 2000;
function verif_launch_button(bcl) {
	var xhr = new XMLHttpRequest();

	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4 && xhr.status == 200) {
			if(xhr.responseText == '2') {
				$id("background").style.display = "none";
				$id("launch_button").value = "currently processing...";
				$id("back_button").disabled = true;
				$id("launch_button").disabled = true;
				$id("launch_button").onclick = function() {};
				get_available_results();
			}
			else if(xhr.responseText == '1') {
				$id("back_button").disabled = false;
				$id("launch_button").disabled = false;
				$id("background").style.display = "none";
				$id("launch_button").value = "see";
				$id("launch_button").onclick = function() {see()};
				get_available_results();
			}
			else {
				$id("back_button").disabled = false;
				$id("launch_button").disabled = false;
				$id("background").style.display = "block";
				$id("launch_button").value = "generate";
				$id("launch_button").onclick = function() {if(confirm("This may take time. Are you sure?")){launch()} };
			}
		}
	};

	nbsensors = $id("nbsensors").value;
	if(choice_com == 0) propagation = get_propagation();
	else if(choice_com == 1) propagation = $id("modele_propagation").value;
	wlanes = $id("wlanes").value;

	xhr.open("POST", "file_exists.php?osm=" + (id_map) + "&propagation=" + (propagation) + "&nbsensors=" + (nbsensors) + "&wlanes=" + (wlanes) + "&modif=" + (modif), true);
	xhr.send(null);

	if(bcl == 1)
		setTimeout(function() {verif_launch_button(1)}, boucle2);
}

function see() {
	nbsensors = $id("nbsensors").value;
	if(choice_com == 0) propagation = get_propagation();
	else if(choice_com == 1) propagation = $id("modele_propagation").value;
	wlanes = $id("wlanes").value;

	$id("message").innerHTML = "";
	$id("back_button").onclick = function() {go_parameters(id_map, 0)};
	$id("upload_result").style.display = "none";
	$id("loader").style.display = "none";
	$id("progress").innerHTML = "";
	$id("dialog").style.display = "none";
	$id("result").style.display = "block";
	$id("csv_link").innerHTML = "<a href='./db/output/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-"+propagation+".csv'>download</a>";
	$id("csv2_link").innerHTML = "<a href='./raw.php?t=2&f=./db/output/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-"+propagation+".csv'>download</a>";
	$id("np_link").innerHTML = "<a href='./db/data/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-sensors.data'>download</a>";
	$id("omnetpp_link").innerHTML = "<a href='./raw.php?t=3&f=./db/data/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-sensors.data'>download</a>";
	$id("sumo_link").innerHTML = "<a href='./db/nets/"+id_map+".net.xml'>download</a>";

	get_analysis();
}

function get_analysis() {
	nbsensors = $id("nbsensors").value;
	if(choice_com == 0) propagation = get_propagation();
	else if(choice_com == 1) propagation = $id("modele_propagation").value;
	wlanes = $id("wlanes").value;
	var val = $id("select_graph_analysis").value;
	$id("graph_analysis").innerHTML = "<br/><br/><img src='./ressources/images/loader.gif' style='width: 220px; height: 19px;'/><br/><br/>";

	var paramsup = "";

	if(val == "8" || val == "9" || val == "10" || val == "25" || val == "26") {
		var saisie = prompt("k= ? :", "2")
		if (saisie!=null) {
			paramsup = saisie;
		}
	}
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4) {
            if(xhr.status == 200) {
				$id("graph_analysis").innerHTML = xhr.responseText;
			}
		}
	};

	xhr.open("POST", "get_analysis_of.php?osm=" + (id_map) + "&propagation=" + (propagation) + "&nbsensors=" + (nbsensors) + "&wlanes=" + (wlanes) + "&type=" + val + "&paramsup=" + paramsup + "&modif=" + (modif), true);
	xhr.send(null);
}


function get_plot() {
	nbsensors = $id("nbsensors").value;
	if(choice_com == 0) propagation = get_propagation();
	else if(choice_com == 1) propagation = $id("modele_propagation").value;
	wlanes = $id("wlanes").value;

	var val = $id("select_plot").value;

	$id("plot_display").innerHTML = "<br/><br/><img src='./ressources/images/loader.gif' style='width: 220px; height: 19px;'/><br/><br/>";

	var paramsup = "";

	if(val == "9" || val == "10" || val == "25" || val == "26") {
		var saisie = prompt("k= ? :", "2")
		if (saisie!=null) {
			paramsup = saisie;
		}
	}

	if(parseInt(val) >= 100 && parseInt(val) < 200) {
		var saisie = prompt("logscale ? (no: empty ; yes: x, y, xy)", "")
		if (saisie!=null) {
			paramsup = saisie;
		}
	}

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4) {
            if(xhr.status == 200) {
				$id("plot_display").innerHTML = xhr.responseText;
			}
		}
	};

	xhr.open("POST", "get_plot_of.php?osm=" + (list_id_map) + "&propagation=" + (propagation) + "&nbsensors=" + (nbsensors) + "&wlanes=" + (wlanes) + "&type=" + val + "&paramsup=" + paramsup + "&modif=" + (modif), true);
	xhr.send(null);
}


function get_plot_2() {
	nbsensors = $id("nbsensors").value;
	if(choice_com == 0) propagation = get_propagation();
	else if(choice_com == 1) propagation = $id("modele_propagation").value;
	wlanes = $id("wlanes").value;

	var val = $id("select_plot_2").value;

	$id("plot_display").innerHTML = "<br/><br/><img src='./ressources/images/loader.gif' style='width: 220px; height: 19px;'/><br/><br/>";

	var paramsup = "";

	if(val == "9" || val == "10" || val == "25" || val == "26") {
		var saisie = prompt("k= ? :", "2")
		if (saisie!=null) {
			paramsup = saisie;
		}
	}

	paramsup = "";

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4) {
            if(xhr.status == 200) {
				$id("plot_display").innerHTML = xhr.responseText;
			}
		}
	};

	xhr.open("POST", "get_plot_2_of.php?osm=" + (list_id_map) + "&propagation=" + (propagation) + "&nbsensors=" + (nbsensors) + "&wlanes=" + (wlanes) + "&type=" + val, true);
	xhr.send(null);
}


function launch() {
	$id("message").innerHTML = "";
	$id("upload_result").style.display = "none";
	boucle = 500;
	get_log();

	$id("loader").style.display = "block";

	var o = $id("progress");
	var progress = o.appendChild(document.createElement("p"));
	progress.appendChild(document.createTextNode("simulation in progress..."));

		$id("progress").style.marginLeft = "0px";
	$id("progress").style.marginRight = "0px";
    $id("progress").style.styleFloat = "right";
    $id("progress").style.cssFloat = "right";

	$id("result").style.display = "none";

	$id("back_button").disabled = true;
	$id("launch_button").disabled = true;

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4) {
			progress.className = (xhr.status == 200 ? "success" : "failure");
			if(xhr.status == 200) {
				verif_launch_button(0);
				verif_modif();
				if(lbackground == 1) {
					go_parameters(id_map, 1);
					get_available_results();
				}
				else {
					$id("loader").style.display = "none";
					$id("progress").innerHTML = "";
					boucle = -1;
			        $id("back_button").disabled = false;
               		$id("launch_button").disabled = false;
					$id("dialog").style.display = "none";
					$id("result").style.display = "block";
					$id("back_button").onclick = function() {go_parameters(id_map, 0)};
					$id("csv_link").innerHTML = "<a href='./db/output/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-"+propagation+".csv'>download</a>";
					$id("csv2_link").innerHTML = "<a href='./raw.php?t=2&f=./db/output/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-"+propagation+".csv'>download</a>";
					$id("np_link").innerHTML = "<a href='./db/data/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-sensors.data'>download</a>";
                	$id("omnetpp_link").innerHTML = "<a href='./raw.php?t=3&f=./db/data/"+id_map+"-"+modif+"-"+wlanes+"-"+nbsensors+"-sensors.data'>download</a>";
                	$id("sumo_link").innerHTML = "<a href='./db/nets/"+id_map+".net.xml'>download</a>";
					get_available_results();
					get_analysis();
				}
			}
		}
	};

	nbsensors = $id("nbsensors").value;
	if(choice_com == 0) propagation = get_propagation();
	else if(choice_com == 1) propagation = $id("modele_propagation").value;
	wlanes = $id("wlanes").value;
	lbackground = 0;
	if($id("launch_background").checked == true)
		lbackground = 1;

	xhr.open("POST", "launch.php?osm=" + (id_map) + "&propagation=" + (propagation) + "&nbsensors=" + (nbsensors) + "&wlanes=" + (wlanes) + "&lbackground=" + (lbackground) + "&modif=" + (modif), true);
	xhr.send(null);
}

var boucle = 500;
function get_log() {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(e) {
		if (xhr.readyState == 4) {
            if(xhr.status == 200) {
				$id("dialog_txt").innerHTML = xhr.responseText;
			}
		}
	};

	xhr.open("POST", "get_log.php?osm=" + id_map, true);
	xhr.send(null);

	if(boucle > 0) {
		$id("dialog").style.display = "block";
		setTimeout(get_log, boucle);
	}
}

function Init() {
	var fileselect = $id("fileselect"),
		filedrag = $id("filedrag");

	fileselect.addEventListener("change", FileSelectHandler, false);

	var xhr = new XMLHttpRequest();
	if (xhr.upload) {

		filedrag.addEventListener("dragover", FileDragHover, false);
		filedrag.addEventListener("dragleave", FileDragHover, false);
		filedrag.addEventListener("drop", FileSelectHandler, false);
		filedrag.style.display = "block";
	}

}

if (window.File && window.FileList && window.FileReader) {
	Init();
}

var map = L.map('map').setView([0, 0], 0);
var smallIcon = L.Icon.Default.extend({
    options: {

    }
});
var smallIcon = new smallIcon();	

L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);
