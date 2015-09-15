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

shell_exec(SUMO_HOME."/bin/netconvert --osm-files ".$absolute_path."db/uploads/$osm --osm.skip-duplicates-check false --keep-edges.postload true --geometry.remove --remove-edges.isolated --remove-edges.by-type highway.residential,highway.service,highway.unclassified,highway.living_street,highway.track,highway.pedestrian,highway.bus_guideway,highway.raceway,highway.footway,highway.bridleway,highway.steps,highway.path,highway.cycleway,highway.proposed,highway.construction,highway.escape,highway.emergency,highway.services,highway.phone,highway.emergency_access_point,highway.crossing,highway.bus_stop --remove-edges.by-vclass private,public_transport,public_emergency,public_authority,public_army,vip,ignoring,passenger,hov,taxi,bus,delivery,transport,lightrail,cityrail,rail_slow,rail_fast,motorcycle,bicycle,pedestrian --tls.join --junctions.join --junctions.join-dist 25 --tls.join-dist 25 --no-turnarounds --no-internal-links --no-turnarounds.tls -o ".$absolute_path."db/nets/$osm.net.xml > /dev/null 2>>".$absolute_path."logs/_errors.log");
?>