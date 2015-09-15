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

if(file_exists("./logs/$osm.log"))
	echo str_replace("\n", "<br/>", file_get_contents("./logs/$osm.log"));
?>


