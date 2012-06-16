<?php
// Footer for FAB theme
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Modifications Copyright (c) 2010 Greg Roach
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: footer.php 12577 2011-11-06 18:03:09Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

echo '</div>'; // <div id="content">
if ($view!='simple') {
	echo '<div id="footer"><div class="block">', contact_links();
	
	//PERSO Add Extra footer
	$f_hook = new WT_Perso_Hook('h_print_footer');
	$f_hook->execute();
	//END PERSO
	
	if (WT_DEBUG || get_gedcom_setting(WT_GED_ID, 'SHOW_STATS')) {
		echo execution_stats();
	}
	echo '</div><a style="font-size:150%; color:#888;" href="', WT_WEBTREES_URL, '" title="', WT_WEBTREES , ' - ', WT_VERSION_TEXT, '">', WT_WEBTREES, '</a></div>';
}
