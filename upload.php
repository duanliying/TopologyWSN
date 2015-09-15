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

$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
if(!ALLOW_UPLOADING) 
    $fn = false;

if ($fn) {
	$truefn = htmlentities(stripslashes($fn), ENT_QUOTES);
	$fn = time();

	$savefile = 'db/uploads/'.$fn;
	if(preg_match("#\.net#i", $truefn))
		$savefile = 'nets/'.$fn.'.net.xml';
	file_put_contents(
		$savefile,
		file_get_contents('php://input')
	);

	$info = "./db/info/$fn.inc.php";
	if(file_exists($info)) @unlink($info);

	$pointeur = fopen($info, 'a+');
	fputs($pointeur, '<?php $tinfo = "'.$truefn.'"; ?>');
	fclose($pointeur);

	echo "$fn";
	exit();
}
?>
