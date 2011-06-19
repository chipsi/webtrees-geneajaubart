<?php
// A sidebar to show extra/non-genealogical information about an individual
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
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
//  $Id$

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.'includes/functions/functions_charts.php';

class extra_info_WT_Module extends WT_Module implements WT_Module_Sidebar {
	// Extend WT_Module
	public function getTitle() {
		return WT_I18N::translate('Extra information');
	}

	// Extend WT_Module
	public function getDescription() {
		return WT_I18N::translate('A sidebar that shows non-genealogical facts and other information about an indivdual.');
	}

	// Implement WT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 10;
	}

	// Implement WT_Module_Sidebar
	public function hasSidebarContent() {
		return true;
	}

	// Implement WT_Module_Sidebar
	public function getSidebarContent() {
		global $WT_IMAGES, $FACT_COUNT, $SHOW_COUNTER;
		
		//$reftags = array ('CHAN', 'IDNO', 'RFN', 'AFN', 'REFN', 'RIN', '_UID');// list of tags that can be displayed in this sidebar block

		$root = WT_Person::getInstance($this->controller->pid);
		if ($root!=null) {
			$this->controller = new WT_Controller_Individual();
			$this->controller->indi=$root;
			$this->controller->pid=$root->getXref();
			$this->setController($this->controller);
		}

		if (!$this->controller->indi->canDisplayDetails()) {
			print_privacy_error();
		} else {
			$indifacts = $this->controller->getIndiFacts();
			if (count($indifacts)==0) {
				echo WT_I18N::translate('There are no Facts for this individual.');
			}
			foreach ($indifacts as $value) {
				if (in_array($value->getTag(), WT_Gedcom_Tag::getReferenceFacts())) {
					print_fact($value);
				}
				$FACT_COUNT++;
			}
		}

		echo '<div id="hitcounter">';
		if ($SHOW_COUNTER && (empty($SEARCH_SPIDER))) {
			//print indi counter only if displaying a non-private person
			require WT_ROOT.'includes/hitcount.php';
			echo WT_I18N::translate('Hit Count:'). ' '. $hitCount;
		}
		echo '</div>';// close #hitcounter
	}
	
	// Implement WT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}

}
