<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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
// $Id: module.php 12348 2011-10-20 21:49:01Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// The "user favorites" module is almost identical to the "gedcom favorites" module
require_once WT_ROOT.WT_MODULES_DIR.'gedcom_favorites/module.php';

class user_favorites_WT_Module extends gedcom_favorites_WT_Module {
	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Favorites" module */ WT_I18N::translate('Display and manage a user’s favorite pages.');
	}

	// Implement class WT_Module_Block
	public function isUserBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return false;
	}

}
