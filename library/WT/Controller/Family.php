<?php
// Controller for the Family Page
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team.  All rights reserved.
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
// $Id: Family.php 12048 2011-07-20 23:00:37Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.'includes/functions/functions_print_facts.php';
require_once WT_ROOT.'includes/functions/functions_import.php';
require_once WT_ROOT.'includes/functions/functions_charts.php';

class WT_Controller_Family extends WT_Controller_Base {
	var $famid = '';
	var $family = null;
	var $difffam = null;
	var $accept_success = false;
	var $reject_success = false;
	var $user = null;
	var $display = false;
	var $famrec = '';
	var $title = '';

	function init() {
		global $Dbwidth, $bwidth, $pbwidth, $pbheight, $bheight;
		$bwidth = $Dbwidth;
		$pbwidth = $bwidth + 12;
		$pbheight = $bheight + 14;

		$this->famid = safe_GET_xref('famid');

		$gedrec = find_family_record($this->famid, WT_GED_ID);

		if (empty($gedrec)) {
			$gedrec = "0 @".$this->famid."@ FAM\n";
		}

		if (find_family_record($this->famid, WT_GED_ID) || find_updated_record($this->famid, WT_GED_ID)!==null) {
			$this->family = new WT_Family($gedrec);
			$this->family->ged_id=WT_GED_ID; // This record is from a file
		} else if (!$this->family) {
			return false;
		}

		$this->famid=$this->family->getXref(); // Correct upper/lower case mismatch

		//-- perform the desired action
		switch($this->action) {
		case 'addfav':
			if (WT_USER_ID && !empty($_REQUEST['gid']) && array_key_exists('user_favorites', WT_Module::getActiveModules())) {
				$favorite = array(
					'username' => WT_USER_NAME,
					'gid'      => $_REQUEST['gid'],
					'type'     => 'FAM',
					'file'     => WT_GEDCOM,
					'url'      => '',
					'note'     => '',
					'title'    => ''
				);
				user_favorites_WT_Module::addFavorite($favorite);
			}
			unset($_GET['action']);
			break;
		case 'accept':
			if (WT_USER_CAN_ACCEPT) {
				accept_all_changes($this->famid, WT_GED_ID);
				$this->accept_success=true;
				//-- check if we just deleted the record and redirect to index
				$gedrec = find_family_record($this->famid, WT_GED_ID);
				if (empty($gedrec)) {
					header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH);
					exit;
				}
				$this->family = new WT_Family($gedrec);
			}
			unset($_GET['action']);
			break;
		case 'undo':
			if (WT_USER_CAN_ACCEPT) {
				reject_all_changes($this->famid, WT_GED_ID);
				$this->reject_success=true;
				$gedrec = find_family_record($this->famid, WT_GED_ID);
				//-- check if we just deleted the record and redirect to index
				if (empty($gedrec)) {
					header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH);
					exit;
				}
				$this->family = new WT_Family($gedrec);
			}
			unset($_GET['action']);
			break;
		}

		//-- if the user can edit and there are changes then get the new changes
		if (WT_USER_CAN_EDIT) {
			$newrec = find_updated_record($this->famid, WT_GED_ID);
			if (!empty($newrec)) {
				$this->difffam = new WT_Family($newrec);
				$this->difffam->setChanged(true);
			}
		}

		$this->family->diffMerge($this->difffam);
	}

	function getFamilyID() {
		return $this->famid;
	}

	// $tags is an array of HUSB/WIFE/CHIL
	function getTimelineIndis($tags) {
		preg_match_all('/\n1 (?:'.implode('|', $tags).') @('.WT_REGEX_XREF.')@/', $this->family->getGedcomRecord(), $matches);
		foreach ($matches[1] as &$match) {
			$match='pids[]='.$match;
		}
		return implode('&amp;', $matches[1]);
	}

	/**
	* return the title of this page
	* @return string the title of the page to go in the <title> tags
	*/
	function getPageTitle() {
		if ($this->family) {
			return $this->family->getFullName();
		} else {
			return WT_I18N::translate('Family');
		}
	}

	/**
	* get edit menu
	*/
	function getEditMenu() {
		$SHOW_GEDCOM_RECORD=get_gedcom_setting(WT_GED_ID, 'SHOW_GEDCOM_RECORD');

		if (!$this->family || $this->family->isMarkedDeleted()) {
			return null;
		}

		// edit menu
		$menu = new WT_Menu(WT_I18N::translate('Edit'), '#', 'menu-fam');
		$menu->addIcon('edit_fam');
		$menu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_large_edit_fam');

		if (WT_USER_CAN_EDIT) {
			// edit_fam / members
			$submenu = new WT_Menu(WT_I18N::translate('Change Family Members'), '#', 'menu-fam-change');
			$submenu->addOnclick("return change_family_members('".$this->getFamilyID()."');");
			$submenu->addIcon('edit_fam');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_edit_fam');
			$menu->addSubmenu($submenu);

			// edit_fam / add child
			$submenu = new WT_Menu(WT_I18N::translate('Add a child to this family'), '#', 'menu-fam-addchil');
			$submenu->addOnclick("return addnewchild('".$this->getFamilyID()."');");
			$submenu->addIcon('edit_fam');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_add_fam');
			$menu->addSubmenu($submenu);

			// edit_fam / reorder_children
			if ($this->family->getNumberOfChildren() > 1) {
				$submenu = new WT_Menu(WT_I18N::translate('Re-order children'), '#', 'menu-fam-orderchil');
				$submenu->addOnclick("return reorder_children('".$this->getFamilyID()."');");
				$submenu->addIcon('edit_fam');
				$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_reord_fam');
				$menu->addSubmenu($submenu);
			}
		}

		// edit/view raw gedcom
		if (WT_USER_IS_ADMIN || $SHOW_GEDCOM_RECORD) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit raw GEDCOM record'), '#', 'menu-fam-editraw');
			$submenu->addOnclick("return edit_raw('".$this->getFamilyID()."');");
			$submenu->addIcon('gedcom');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_edit_raw');
			$menu->addSubmenu($submenu);
		} elseif ($SHOW_GEDCOM_RECORD) {
			$submenu = new WT_Menu(WT_I18N::translate('View GEDCOM Record'), '#', 'menu-fam-viewraw');
			$submenu->addIcon('gedcom');
			if (WT_USER_CAN_EDIT) {
				$submenu->addOnclick("return show_gedcom_record('new');");
			} else {
				$submenu->addOnclick("return show_gedcom_record();");
			}
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_edit_raw');
			$menu->addSubmenu($submenu);
		}

		// delete
		if (WT_USER_CAN_EDIT) {
			$submenu = new WT_Menu(WT_I18N::translate('Delete family'), '#', 'menu-fam-del');
			$submenu->addOnclick("if (confirm('".WT_I18N::translate('Deleting the family will unlink all of the individuals from each other but will leave the individuals in place.  Are you sure you want to delete this family?')."')) return delete_family('".$this->getFamilyID()."'); else return false;");
			$submenu->addIcon('remove');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_delete');
			$menu->addSubmenu($submenu);
		}

		// add to favorites
		if (array_key_exists('user_favorites', WT_Module::getActiveModules())) {
			$submenu = new WT_Menu(
				/* I18N: Menu option.  Add [the current page] to the list of favorites */ WT_I18N::translate('Add to favorites'),
				$this->family->getHtmlUrl()."&amp;action=addfav&amp;gid=".$this->getFamilyID(),
				'menu-fam-addfav'
			);
			$submenu->addIcon('favorites');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_fav');
			$menu->addSubmenu($submenu);
		}

		//-- get the link for the first submenu and set it as the link for the main menu
		if (isset($menu->submenus[0])) {
			$link = $menu->submenus[0]->onclick;
			$menu->addOnclick($link);
		}
		return $menu;
	}
}
