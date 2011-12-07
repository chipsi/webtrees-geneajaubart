<?php
// Footer for webtrees theme
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
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
// $Id: footer.php 12696 2011-11-11 00:56:58Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

echo '</div>'; // <div id="content">
if ($view!='simple') {
	echo '<div id="footer">';
	echo contact_links();
	echo '<p class="logo">';
	echo '<a href="', WT_WEBTREES_URL, '" target="_blank">';
	echo '<img src="', $WT_IMAGES['webtrees'], '" width="100" height="21" alt="', WT_WEBTREES, '" title="', WT_WEBTREES, ' ', WT_VERSION_TEXT , '" /></a>';
	echo '</p>';
	if (WT_DEBUG || get_gedcom_setting(WT_GED_ID, 'SHOW_STATS')) {
		echo execution_stats();
	}
	if (exists_pending_change()) {
		echo '<a href="#" onclick="window.open(\'edit_changes.php\', \'_blank\', \'width=600, height=500, resizable=1, scrollbars=1\'); return false;">';
		echo '<p class="error center">', WT_I18N::translate('There are pending changes for you to moderate.'), '</p>';
		echo '</a>';
	}
	echo '</div>'; // <div id="footer">
}
