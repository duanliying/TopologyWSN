# Characterizing the Topology of an Urban Wireless Sensor Network for Road Traffic Management

This project is a web application that simulates the deployment of a wireless sensor network in a city. It uses OpenStreetMap and SUMO maps and deploy sensors based on customizable options and different path loss models. In addition to the data generation, we proposes tools that analyze the topological and structural proprieties of the network.

If you use this project or one of its component, we would appreciate a citation of our work:

S. Faye, C. Chaudet, "Characterizing the Topology of an Urban Wireless Sensor Network for Road Traffic Management", in IEEE Transactions on Vehicular Technology, 2015. 



# LICENSING

Copyright (C) 2014 Faye Sébastien <m@sfaye.com>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

For more informations see ./LICENCE file.



# HOW TO USE

REQUIREMENTS:

  * A web server with PHP >= 5 (e.g. MAMP on Mac OS X to run the program locally)
  * Python 2.7
  * All the programs and libraries listed in « ACKNOWLEDGMENTS » bellow

CONFIGURATION:

  * See ./_config.inc.php

STRUCTURE:

  * ./db/ -> maps and generated data
  * ./logs/ -> logs (gnuplot and maps).
  * ./py/ -> Python scripts (SUMO / NetworkX)
  * ./ressources/ -> css-js-images files



# CONTACT

Fell free to contact me via e-mail (m@sfaye.com) or using the following project web page: http://g.sfaye.com/



# ACKNOWLEDGMENTS

This program uses the following projects and libraries:

 * gnuplot
   (c) 1986 - 1993, 1998, 2004, Thomas Williams, Colin Kelley
   http://www.gnuplot.info/

 * matplotlib
   (c) 2002 - 2012 John Hunter, Darren Dale, Eric Firing, Michael Droettboom and the matplotlib development team; 2012 - 2013 The matplotlib development team.
   http://matplotlib.org/

 * NetworkX - High-productivity software for complex networks
   (c) Copyright 2013, NetworkX developer team.
   https://networkx.github.io/

 * SciPy
   (c) 2014 SciPy developers.
   http://www.scipy.org/

 * SUMO - Simulation of Urban MObulity
   (c) 2011-2014, German Aerospace Center, Institute of Transportation Systems
   http://sumo-sim.org/

 * Leaflet
  http://leafletjs.com/

 * OpenStreetMap
   http://www.openstreetmap.org/
