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
// $Id: module.php 12503 2011-11-03 11:58:38Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class relative_ext_report_WT_Module extends WT_Module implements WT_Module_Report {
	// Extend class WT_Module
	public function getTitle() {
		// This text also appears in the .XML file - update both together
		return /* I18N: Name of a report */ WT_I18N::translate('Related individuals');
	}

	// Extend class WT_Module
	public function getDescription() {
		// This text also appears in the .XML file - update both together
		return /* I18N: Description of the "Related individuals" module */ WT_I18N::translate('A report of the individuals that are closely related to an individual.');
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Report - a module can provide many reports
	public function getReportMenus() {
		global $controller;

		if ($controller instanceof WT_Controller_Family && $controller->record instanceof WT_Family) {
			// We are on a family page
			$pid='&amp;famid='.$controller->record->getXref();
		} elseif ($controller instanceof WT_Controller_Individual && $controller->record instanceof WT_Individual) {
			// We are on an individual page
			$pid='&amp;pid='.$controller->record->getXref();
		} elseif ($controller && isset($controller->rootid)) {
			// We are on a chart page
			$pid='&amp;pid='.$controller->rootid;
		} else {
			$pid='';
		}

		$menus=array();
		$menu=new WT_Menu(
			$this->getTitle(),
			'reportengine.php?ged='.WT_GEDURL.'&amp;action=setup&amp;report='.WT_MODULES_DIR.$this->getName().'/report.xml'.$pid,
			'menu-report-'.$this->getName()
		);
		$menu->addIcon('place');
		$menu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_reports');
		$menus[]=$menu;

		return $menus;
	}
}
