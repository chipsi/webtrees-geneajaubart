<?php
// Controller for the Repository Page
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
// $Id: Repository.php 12212 2011-09-25 08:25:29Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.'includes/functions/functions_print_facts.php';
require_once WT_ROOT.'includes/functions/functions_import.php';

class WT_Controller_Repository extends WT_Controller_Base {
	var $rid;
	var $repository = null;
	var $diffrepository = null;

	function init() {
		$this->rid = safe_GET_xref('rid');

		$gedrec=find_other_record($this->rid, WT_GED_ID);
		if (WT_USER_CAN_EDIT) {
			$newrec=find_updated_record($this->rid, WT_GED_ID);
		} else {
			$newrec=null;
		}

		if ($gedrec===null) {
			if ($newrec===null) {
				// Nothing to see here.
				return;
			} else {
				// Create a dummy record from the first line of the new record.
				// We need it for diffMerge(), getXref(), etc.
				list($gedrec)=explode("\n", $newrec);
			}
		}

		//-- perform the desired action
		switch($this->action) {
		case 'addfav':
			if (WT_USER_ID && !empty($_REQUEST['gid']) && array_key_exists('user_favorites', WT_Module::getActiveModules())) {
				$favorite = array(
					'username' => WT_USER_NAME,
					'gid'      => $_REQUEST['gid'],
					'type'     => 'REPO',
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
				accept_all_changes($this->rid, WT_GED_ID);
				$gedrec=find_other_record($this->rid, WT_GED_ID);
				$newrec=null;
				if ($gedrec===null) {
					header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH);
					exit;
				}
				$this->repository = new WT_Repository($gedrec);
			}
			unset($_GET['action']);
			break;
		case 'undo':
			if (WT_USER_CAN_ACCEPT) {
				reject_all_changes($this->rid, WT_GED_ID);
				$gedrec=find_other_record($this->rid, WT_GED_ID);
				$newrec=null;
				if ($gedrec===null) {
					header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH);
					exit;
				}
			}
			unset($_GET['action']);
			break;
		}

		$this->repository = new WT_Repository($gedrec);

		// If there are pending changes, merge them in.
		if ($newrec!==null) {
			$this->diffrepository=new WT_Repository($newrec);
			$this->diffrepository->setChanged(true);
			$this->repository->diffMerge($this->diffrepository);
		}
		$this->rid=$this->repository->getXref(); // We may have requested X1234, but found x1234
	}

	/**
	* get the title for this page
	* @return string
	*/
	function getPageTitle() {
		if ($this->repository) {
			return $this->repository->getFullName();
		} else {
			return WT_I18N::translate('Repository');
		}
	}

	/**
	* get edit menu
	*/
	function getEditMenu() {
		$SHOW_GEDCOM_RECORD=get_gedcom_setting(WT_GED_ID, 'SHOW_GEDCOM_RECORD');

		if (!$this->repository || $this->repository->isMarkedDeleted()) {
			return null;
		}

		// edit menu
		$menu = new WT_Menu(WT_I18N::translate('Edit'), '#', 'menu-repo');
		$menu->addIcon('edit_repo');
		$menu->addClass('menuitem', 'menuitem_hover', 'submenu', 'icon_large_edit_repo');

		if (WT_USER_CAN_EDIT) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit repository'), '#', 'menu-repo-edit');
			$submenu->addOnclick('return edit_source(\''.$this->rid.'\');');
			$submenu->addIcon('edit_repo');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_edit_repo');
			$menu->addSubmenu($submenu);
		}

		// edit/view raw gedcom
		if (WT_USER_IS_ADMIN || $SHOW_GEDCOM_RECORD) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit raw GEDCOM record'), '#', 'menu-repo-editraw');
			$submenu->addOnclick("return edit_raw('".$this->rid."');");
			$submenu->addIcon('gedcom');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_edit_raw');
			$menu->addSubmenu($submenu);
		} elseif ($SHOW_GEDCOM_RECORD) {
			$submenu = new WT_Menu(WT_I18N::translate('View GEDCOM Record'), '#', 'menu-repo-viewraw');
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
			$submenu = new WT_Menu(WT_I18N::translate('Delete'), '#', 'menu-repo-del');
			$submenu->addOnclick("if (confirm('".addslashes(WT_I18N::translate('Are you sure you want to delete “%s”?', $this->repository->getFullName()))."')) return deleterepository('".$this->repository->getXref()."'); else return false;");
			$submenu->addIcon('remove');
			$submenu->addClass('submenuitem', 'submenuitem_hover', 'submenu', 'icon_small_delete');
			$menu->addSubmenu($submenu);
		}

		// add to favorites
		if (array_key_exists('user_favorites', WT_Module::getActiveModules())) {
			$submenu = new WT_Menu(
				WT_I18N::translate('Add to favorites'),
				$this->repository->getHtmlUrl()."&amp;action=addfav&amp;gid=".$this->rid,
				'menu-repo-addfav'
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
