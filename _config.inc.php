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

# SUMO home directory
define("SUMO_HOME", "/home/SUMO/src-svn");

# Python 2.7
define("PYTHON", "/usr/bin/python");

# Link to gnuplot
define("GNUPLOT", "/usr/bin/gnuplot");

# Allows uploading a new map or not
define("ALLOW_UPLOADING", true);




#
# !! DO NOT EDIT !!
if(!file_exists(SUMO_HOME.'/bin/sumo') || !file_exists(PYTHON) || !file_exists(GNUPLOT)) {
    echo "Error: a resource is missing. Please edit the configuration file.";
    exit;
}
define("VERSION", file_get_contents("./VERSION"));
?>
