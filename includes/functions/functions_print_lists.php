<?php
// Functions for printing lists
//
// Various printing functions for printing lists
// used on the indilist, famlist, find, and search pages.
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
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
// $Id: functions_print_lists.php 13436 2012-02-12 22:02:35Z nigel $
// @version: p_$Revision$ $Date$
// $HeadURL$

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.'includes/functions/functions_places.php';

// print a table of individuals
function format_indi_table($datalist, $option='') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $WT_IMAGES, $SEARCH_SPIDER, $MAX_ALIVE_AGE, $controller;

	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
	if ($option=='MARR_PLAC') return;
	$html = '';
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
			jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
			var oTable'.$table_id.' = jQuery("#'.$table_id.'").dataTable( {
				"sDom": \'<"H"<"filtersH_'.$table_id.'"><"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_'.$table_id.'">>\',
				'.WT_I18N::datatablesI18N().',
				"bJQueryUI": true,
				"bAutoWidth":false,
				"bProcessing": true,
				"bRetrieve": true,
				"aoColumns": [
					/*  0 givn      */ {"iDataSort": 2},
					/*  1 surn      */ {"iDataSort": 3},
					/*  2 GIVN,SURN */ {"sType": "unicode", "bVisible": false},
					/*  3 SURN,GIVN */ {"sType": "unicode", "bVisible": false},
					/*  4 sosa      */ {"iDataSort": 5, "sClass": "center", "bVisible": '.($option=='sosa'?'true':'false').'},
					/*  5 SOSA      */ {"sType": "numeric", "bVisible": false},
					/* PERSO Modify table to include IsSourced module */
					/*  6 birt date */ {"iDataSort": 7, "sClass": "center"},
					/*  7 BIRT:DATE */ {"bVisible": false},
					/*  8 anniv     */ {"bSortable": false, "sClass": "center"},
					/*  9 birt plac */ {"sType": "unicode", "sClass": "center"},
					/* 10 birt sour */ { "iDataSort" : 11, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
					/* 11 sort sour */ { "bVisible": false},
					/* 12 children  */ {"iDataSort": 13, "sClass": "center"},
					/* 13 children  */ {"sType": "numeric", "bVisible": false},
					/* 14 deat date */ {"iDataSort": 15, "sClass": "center"},
					/* 15 DEAT:DATE */ {"bVisible": false},
					/* 16 anniv     */ {"bSortable": false, "sClass": "center"},
					/* 17 age       */ {"iDataSort": 18, "sClass": "center"},
					/* 18 AGE       */ {"sType": "numeric", "bVisible": false},
					/* 19 deat plac */ {"sType": "unicode", "sClass": "center"},
					/* 20 deat sour */ { "iDataSort" : 21, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
					/* 21 sort sour */ { "bVisible": false},
					/* 22 CHAN      */ {"bVisible": '.($SHOW_LAST_CHANGE?'true':'false').'},
					/* 23 SEX       */ {"bVisible": false},
					/* 24 BIRT      */ {"bVisible": false},
					/* 25 DEAT      */ {"bVisible": false},
					/* 26 TREE      */ {"bVisible": false}
					/* END PERSO */
				],
				"aaSorting": [['.($option=='sosa'?'4, "asc"':'1, "asc"').']],
				"iDisplayLength": 20,
				"sPaginationType": "full_numbers"
			});
	
			jQuery("div.filtersH_'.$table_id.'").html("'.addslashes(
				'<button type="button" id="SEX_M_'.    $table_id.'" class="ui-state-default SEX_M" title="'.    WT_I18N::translate('Show only males.').'">&nbsp;'.WT_Person::sexImage('M', 'small').'&nbsp;</button>'.
				'<button type="button" id="SEX_F_'.    $table_id.'" class="ui-state-default SEX_F" title="'.    WT_I18N::translate('Show only females.').'">&nbsp;'.WT_Person::sexImage('F', 'small').'&nbsp;</button>'.
				'<button type="button" id="SEX_U_'.    $table_id.'" class="ui-state-default SEX_U" title="'.    WT_I18N::translate('Show only persons of whom the gender is not known.').'">&nbsp;'.WT_Person::sexImage('U', 'small').'&nbsp;</button>'.
				'<button type="button" id="DEAT_N_'.   $table_id.'" class="ui-state-default DEAT_N" title="'.   WT_I18N::translate('Show people who are alive or couples where both partners are alive.').'">'.WT_I18N::translate('Alive').'</button>'.
				'<button type="button" id="DEAT_Y_'.   $table_id.'" class="ui-state-default DEAT_Y" title="'.   WT_I18N::translate('Show people who are dead or couples where both partners are deceased.').'">'.WT_I18N::translate('Dead').'</button>'.
				'<button type="button" id="DEAT_YES_'. $table_id.'" class="ui-state-default DEAT_YES" title="'. WT_I18N::translate('Show people who died more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('DEAT').'&gt;100</button>'.
				'<button type="button" id="DEAT_Y100_'.$table_id.'" class="ui-state-default DEAT_Y100" title="'.WT_I18N::translate('Show people who died within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('DEAT').'&lt;=100</button>'.
				'<button type="button" id="BIRT_YES_'. $table_id.'" class="ui-state-default BIRT_YES" title="'. WT_I18N::translate('Show persons born more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('BIRT').'&gt;100</button>'.
				'<button type="button" id="BIRT_Y100_'.$table_id.'" class="ui-state-default BIRT_Y100" title="'.WT_I18N::translate('Show persons born within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('BIRT').'&lt;=100</button>'.
				'<button type="button" id="TREE_R_'   .$table_id.'" class="ui-state-default TREE_R" title="'.   WT_I18N::translate('Show «roots» couples or individuals.  These people may also be called «patriarchs».  They are individuals who have no parents recorded in the database.').'">'.WT_I18N::translate('Roots').'</button>'.
				'<button type="button" id="TREE_L_'.   $table_id.'" class="ui-state-default TREE_L" title="'.   WT_I18N::translate('Show «leaves» couples or individuals.  These are individuals who are alive but have no children recorded in the database.').'">'.WT_I18N::translate('Leaves').'</button>'.
				'<button type="button" id="RESET_'.    $table_id.'" class="ui-state-default RESET" title="'.    WT_I18N::translate('Reset to the list defaults.').'">'.WT_I18N::translate('Reset').'</button>'
			).'");
	
			jQuery("div.filtersF_'.$table_id.'").html("'.addslashes(
				'<button type="button" class="ui-state-default" id="cb_parents_indi_list_table" onclick="jQuery(\'div.parents_indi_list_table_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'.WT_I18N::translate('Show parents').'</button>'.
				'<button type="button" class="ui-state-default" id="charts_indi_list_table" onclick="jQuery(\'div.indi_list_table-charts_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'.WT_I18N::translate('Show statistics charts').'</button>'
			).'");
	
			/* Add event listeners for filtering inputs */
			/* PERSO Modify table to include IsSourced module */
			jQuery("#SEX_M_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("M", 23 );
				jQuery("#SEX_M_'.$table_id.'").addClass("ui-state-active");
				jQuery("#SEX_F_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#SEX_U_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#SEX_F_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("F", 23 );
				jQuery("#SEX_M_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#SEX_F_'.$table_id.'").addClass("ui-state-active");
				jQuery("#SEX_U_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#SEX_U_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("U", 23 );
				jQuery("#SEX_M_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#SEX_F_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#SEX_U_'.$table_id.'").addClass("ui-state-active");
			});
			jQuery("#BIRT_YES_'. $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("YES", 24 );
				jQuery("#BIRT_YES_'.$table_id.'").addClass("ui-state-active");
				jQuery("#BIRT_Y100_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#BIRT_Y100_'.$table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("Y100", 24 );
				jQuery("#BIRT_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#BIRT_Y100_'.$table_id.'").addClass("ui-state-active");
			});
			jQuery("#DEAT_N_'.   $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("N", 25 );
				jQuery("#DEAT_N_'.$table_id.'").addClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y100_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#DEAT_Y_'.   $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("^Y", 25, true, false );
				jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").addClass("ui-state-active");
				jQuery("#DEAT_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y100_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#DEAT_YES_'. $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("YES", 25 );
				jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_YES_'.$table_id.'").addClass("ui-state-active");
				jQuery("#DEAT_Y100_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#DEAT_Y100_'.$table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("Y100", 25 );
				jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y100_'.$table_id.'").addClass("ui-state-active");
			});
			jQuery("#TREE_R_'.   $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("R", 26 );
				jQuery("#TREE_R_'.$table_id.'").addClass("ui-state-active");
				jQuery("#TREE_L_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#TREE_L_'.   $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("L", 26 );
				jQuery("#TREE_R_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#TREE_L_'.$table_id.'").addClass("ui-state-active");
			});	
			jQuery("#RESET_'.    $table_id.'").click( function() {
				for (i=23; i<=26; i++){
					oTable'.$table_id.'.fnFilter("", i );
				};
				jQuery("div.filtersH_'.$table_id.' button").removeClass("ui-state-active");
			});
			/* END PERSO */

			/* This code is a temporary fix for Datatables bug http://www.datatables.net/forums/discussion/4730/datatables_sort_wrapper-being-added-to-columns-with-bsortable-false/p1*/
			jQuery("th div span:eq(3)").css("display", "none");
			jQuery("th div:eq(3)").css("margin", "auto").css("text-align", "center");
			jQuery("th span:eq(8)").css("display", "none");
			jQuery("th div:eq(8)").css("margin", "auto").css("text-align", "center");
			
			jQuery(".indi-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');

	$stats = new WT_Stats($GEDCOM);

	// Bad data can cause "longest life" to be huge, blowing memory limits
	$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;

	//-- init chart data
	for ($age=0; $age<=$max_age; $age++) $deat_by_age[$age]="";
	for ($year=1550; $year<2030; $year+=10) $birt_by_decade[$year]="";
	for ($year=1550; $year<2030; $year+=10) $deat_by_decade[$year]="";
	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="indi-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>';
	$html .= '<th>GIVN</th>';
	$html .= '<th>SURN</th>';
	$html .= '<th>'. /* I18N: Abbreviation for "Sosa-Stradonitz number".  This is a person's surname, so may need transliterating into non-latin alphabets. */ WT_I18N::translate('Sosa'). '</th>';
	$html .= '<th>SOSA</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('BIRT'). '</th>';
	$html .= '<th>SORT_BIRT</th>';
	$html .= '<th><img src="'. $WT_IMAGES['reminder']. '" alt="'. WT_I18N::translate('Anniversary'). '" title="'. WT_I18N::translate('Anniversary'). '"></th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('PLAC'). '</th>';
	//PERSO Modify table to include IsSourced module
	if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
		$html .= '<th><img src="'.$WT_IMAGES['source'].'" alt="'.WT_I18N::translate('Sourced birth').'" title="'.WT_I18N::translate('Sourced birth').'" border="0" /></th>'.
			'<th>SORT_BIRTSC</th>';
	} else {
		$html .=  '<th></th><th></th>';
	}
	//END PERSO
	$html .= '<th><img src="'. $WT_IMAGES['children']. '" alt="'. WT_I18N::translate('Children'). '" title="'. WT_I18N::translate('Children'). '"></th>';
	$html .= '<th>NCHI</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('DEAT'). '</th>';
	$html .= '<th>SORT_DEAT</th>';
	$html .= '<th><img src="'. $WT_IMAGES['reminder']. '" alt="'. WT_I18N::translate('Anniversary'). '" title="'. WT_I18N::translate('Anniversary'). '"></th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>';
	$html .= '<th>AGE</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('PLAC'). '</th>';
	//PERSO Modify table to include IsSourced module
	if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
		$html .=  '<th><img src="'.$WT_IMAGES['source'].'" alt="'.WT_I18N::translate('Sourced death').'" title="'.WT_I18N::translate('Sourced death').'" border="0" /></th>'.
			'<th>SORT_DEATSC</th>';
	} else {
		$html .=  '<th></th><th></th>';
	}
	//END PERSO
	$html .= '<th' .($SHOW_LAST_CHANGE?'':''). '>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th>SEX</th>';
	$html .= '<th>BIRT</th>';
	$html .= '<th>DEAT</th>';
	$html .= '<th>TREE</th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$d100y=new WT_Date(date('Y')-100);  // 100 years ago
	$dateY = date('Y');
	$unique_indis=array(); // Don't double-count indis with multiple names.
	foreach ($datalist as $key=>$value) {
		if (is_object($value)) { // Array of objects
			$person=$value;
		} elseif (!is_array($value)) { // Array of IDs
			$person = WT_Person::getInstance($value);
		} else { // Array of search results
			$gid = $key;
			if (isset($value['gid'])) $gid = $value['gid']; // from indilist
			if (isset($value[4])) $gid = $value[4]; // from indilist ALL
			$person = WT_Person::getInstance($gid);
		}
		if (is_null($person)) continue;
		if ($person->getType() !== 'INDI') continue;
		if (!$person->canDisplayName()) {
			continue;
		}
		//PERSO Create decorator for Individual
		$dperson = new WT_Perso_Person($person);
		//END PERSO
		//-- place filtering
		if ($option=='BIRT_PLAC' && strstr($person->getBirthPlace(), $filter)===false) continue;
		if ($option=='DEAT_PLAC' && strstr($person->getDeathPlace(), $filter)===false) continue;
		$html .= '<tr>';
		//-- Indi name(s)
		$html .= '<td colspan="2">';
		foreach ($person->getAllNames() as $num=>$name) {
			if ($name['type']=='NAME') {
				$title='';
			} else {
				$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $person)).'"';
			}
			if ($num==$person->getPrimaryName()) {
				$class=' class="name2"';
				$sex_image=$person->getSexImage();
				list($surn, $givn)=explode(',', $name['sort']);
			} else {
				$class='';
				$sex_image='';
			}
			//PERSO Add Sosa Image
			$html .= '<a '. $title. ' href="'. $person->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dperson->getSosaNumbers(), 1, 'smaller').'<br>';
			//END PERSO
		}
		// Indi parents
		$html .= $person->getPrimaryParentsNames('parents_indi_list_table_'.$table_id.' details1', 'none');
		$html .= '</td>';
		// Dummy column to match colspan in header
		$html .= '<td style="display:none;"></td>';
		//-- GIVN/SURN
		// Use "AAAA" as a separator (instead of ",") as JavaScript.localeCompare() ignores
		// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
		// Similarly, @N.N. would sort as NN.
		$html .= '<td>'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
		$html .= '<td>'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
		//-- SOSA
		if ($option=='sosa') {
			$html .= '<td><a href="relationship.php?pid1='. $datalist[1]. '&amp;pid2='. $person->getXref(). '" title="'. WT_I18N::translate('Relationships'). '">'. WT_I18N::number($key). '</a></td><td>'. $key. '</td>';
		} else {
			$html .= '<td>&nbsp;</td><td>0</td>';
		}
		//-- Birth date
		$html .= '<td>';
		if ($birth_dates=$person->getAllBirthDates()) {
			foreach ($birth_dates as $num=>$birth_date) {
				if ($num) {
					$html .= '<br>';
				}
				$html .= $birth_date->Display(!$SEARCH_SPIDER);
			}
			if ($birth_dates[0]->gregorianYear()>=1550 && $birth_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
				$birt_by_decade[floor($birth_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
			}
		} else {
			$birth_date=$person->getEstimatedBirthDate();
			$birth_jd=$birth_date->JD();
			if ($SHOW_EST_LIST_DATES) {
				$html .= $birth_date->Display(!$SEARCH_SPIDER);
			} else {
				$html .= '&nbsp;';
			}
			$birth_dates[0]=new WT_Date('');
		}
		$html .= '</td>';
		//-- Event date (sortable)hidden by datatables code
		$html .= '<td>'. $birth_date->JD(). '</td>';
		//-- Birth anniversary
		$html .= '<td>'.WT_Date::getAge($birth_dates[0], null, 2).'</td>';
		//-- Birth place
		$html .= '<td>';
		foreach ($person->getAllBirthPlaces() as $n=>$birth_place) {
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= get_place_short($birth_place);
			} else {
				$html .= '<a href="'. get_place_url($birth_place). '" title="'. $birth_place. '">';
				$html .= highlight_search_hits(get_place_short($birth_place)). '</a>';
			}
		}
		$html .= '</td>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$isBSourced = $dperson->isBirthSourced();
			$html .= '<td>'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $isBSourced, 'BIRT', 1, 'medium').'</td>'.
				'<td>'.$isBSourced.'</td>';
		} else {
			$html .= '<td>&nbsp;</td>'.
				'<td></td>';
		}
		//END PERSO
		//-- Number of children
		$nchi=$person->getNumberOfChildren();
		$html .= '<td>'. WT_I18N::number($nchi). '</td><td>'. $nchi. '</td>';
		//-- Death date
		$html .= '<td>';
		if ($death_dates=$person->getAllDeathDates()) {
			foreach ($death_dates as $num=>$death_date) {
				if ($num) {
					$html .= '<br>';
				}
				$html .= $death_date->Display(!$SEARCH_SPIDER);
			}
			if ($death_dates[0]->gregorianYear()>=1550 && $death_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
				$deat_by_decade[floor($death_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
			}
		} else {
			$death_date=$person->getEstimatedDeathDate();
			$death_jd=$death_date->JD();
			if ($SHOW_EST_LIST_DATES) {
				$html .= $death_date->Display(!$SEARCH_SPIDER);
			} else if ($person->isDead()) {
				$html .= WT_I18N::translate('yes');
			} else {
				$html .= '&nbsp;';
			}
			$death_dates[0]=new WT_Date('');
		}
		$html .= '</td>';
		//-- Event date (sortable)hidden by datatables code
		$html .= '<td>'. $death_date->JD(). '</td>';
		//-- Death anniversary
		$html .= '<td>'.WT_Date::getAge($death_dates[0], null, 2).'</td>';
		//-- Age at death
		$age=WT_Date::getAge($birth_dates[0], $death_dates[0], 0);
		if (!isset($unique_indis[$person->getXref()]) && $age>=0 && $age<=$max_age) {
			$deat_by_age[$age].=$person->getSex();
		}
		// Need both display and sortable age
		$html .= '<td>' . WT_Date::getAge($birth_dates[0], $death_dates[0], 2) . '</td><td>' . WT_Date::getAge($birth_dates[0], $death_dates[0], 1) . '</td>';
		//-- Death place
		$html .= '<td>';
		foreach ($person->getAllDeathPlaces() as $n=>$death_place) {
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= get_place_short($death_place);
			} else {
				$html .= '<a href="'. get_place_url($death_place). '" title="'. $death_place. '">';
				$html .= highlight_search_hits(get_place_short($death_place)). '</a>';
			}
		}
		$html .= '</td>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			if($person->isDead()){
				$isDSourced = $dperson->isDeathSourced();
				$html .= '<td>'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $isDSourced, 'DEAT', 1, 'medium').'</td>'.
					'<td>'.$isDSourced.'</td>';
			}
			else{
				$html .='<td>&nbsp;</td>'.
					'<td>-99</td>';
			}
		} else {
			$html .= '<td>&nbsp;</td>'.
				'<td></td>';
		}
		//END PERSO
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $person->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Sorting by gender
		$html .= '<td>';
		$html .= $person->getSex();
		$html .= '</td>';
		//-- Filtering by birth date
		$html .= '<td>';
		if (!$person->canDisplayDetails() || WT_Date::Compare($birth_dates[0], $d100y)>0) {
			$html .= 'Y100';
		} else {
			$html .= 'YES';
		}
		$html .= '</td>';
		//-- Filtering by death date
		$html .= '<td>';
		if ($person->isDead()) {
			if (WT_Date::Compare($death_dates[0], $d100y)>0) {
				$html .= 'Y100';
			} else {
				$html .= 'YES';
			}
		} else {
			$html .= 'N';
		}
		$html .= '</td>';
		//-- Roots or Leaves ?
		$html .= '<td>';
		if (!$person->getChildFamilies()) { $html .= 'R'; }  // roots
		elseif (!$person->isDead() && $person->getNumberOfChildren()<1) { $html .= 'L'; } // leaves
		else { $html .= '&nbsp;'; }
		$html .= '</td>';
		$html .= '</tr>';
		$unique_indis[$person->getXref()]=true;
	}
	$html .= '</tbody></table>';
	//-- charts
	$html .= '<div class="indi_list_table-charts_'. $table_id. '" style="display:none">
		<table class="list-charts"><tr><td>'.
		print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')).
		'</td><td>'.
		print_chart_by_decade($deat_by_decade, WT_I18N::translate('Decade of death')).
		'</td></tr><tr><td colspan="2">'.
		print_chart_by_age($deat_by_age, WT_I18N::translate('Age related to death year')).
		'</td></tr></table>
		</div>
		</div>'; // Close "indi-list"
		
	return $html;
}

// print a table of families
function format_fam_table($datalist, $option='') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $WT_IMAGES, $SEARCH_SPIDER, $controller;
	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	if ($option=='BIRT_PLAC' || $option=='DEAT_PLAC') return;
	$html = '';

	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			var oTable'.$table_id.'=jQuery("#'.$table_id.'").dataTable( {
				"sDom": \'<"H"<"filtersH_'.$table_id.'"><"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_'.$table_id.'">>\',
				'.WT_I18N::datatablesI18N().',
				"bJQueryUI": true,
				"bAutoWidth":false,
				"bProcessing": true,
				"bRetrieve": true,
				"aoColumns": [
					/*  0 husb givn */ {"iDataSort": 2},
					/*  1 husb surn */ {"iDataSort": 3},
					/*  2 GIVN,SURN */ {"sType": "unicode", "bVisible": false},
					/*  3 SURN,GIVN */ {"sType": "unicode", "bVisible": false},
					/*  4 age       */ {"iDataSort": 5, "sClass": "center"},
					/*  5 AGE       */ {"sType": "numeric", "bVisible": false},
					/*  6 wife givn */ {"iDataSort": 8},
					/*  7 wife surn */ {"iDataSort": 9},
					/*  8 GIVN,SURN */ {"sType": "unicode", "bVisible": false},
					/*  9 SURN,GIVN */ {"sType": "unicode", "bVisible": false},
					/* 10 age       */ {"iDataSort": 11, "sClass": "center"},
					/* 11 AGE       */ {"sType": "numeric", "bVisible": false},
					/* PERSO Modify table to include IsSourced module */
					/* 12 marr date */ {"iDataSort": 13, "sClass": "center"},
					/* 13 MARR:DATE */ {"bVisible": false},
					/* 14 anniv     */ {"bSortable": false, "sClass": "center"},
					/* 15 marr plac */ {"sType": "unicode", "sClass": "center"},
					/* 16 birt sour */ { "iDataSort" : 17, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
					/* 17 sort sour */ { "bVisible": false},
					/* 18 children  */ {"iDataSort": 19, "sClass": "center"},
					/* 19 NCHI      */ {"sType": "numeric", "bVisible": false},
					/* 20 CHAN      */ {"bVisible": '.($SHOW_LAST_CHANGE?'true':'false').'},
					/* 21 MARR      */ {"bVisible": false},
					/* 22 DEAT      */ {"bVisible": false},
					/* 23 TREE      */ {"bVisible": false}
					/* END PERSO */
				],
				"aaSorting": [[1, "asc"]],
				"iDisplayLength": 20,
				"sPaginationType": "full_numbers"
		   });

			jQuery("div.filtersH_'.$table_id.'").html("'.addslashes(
				'<button type="button" id="DEAT_N_'.    $table_id.'" class="ui-state-default DEAT_N" title="'.    WT_I18N::translate('Show people who are alive or couples where both partners are alive.').'">'.WT_I18N::translate('Both alive').'</button>'.
				'<button type="button" id="DEAT_W_'.    $table_id.'" class="ui-state-default DEAT_W" title="'.    WT_I18N::translate('Show couples where only the female partner is deceased.').'">'.WT_I18N::translate('Widower').'</button>'.
				'<button type="button" id="DEAT_H_'.    $table_id.'" class="ui-state-default DEAT_H" title="'.    WT_I18N::translate('Show couples where only the male partner is deceased.').'">'.WT_I18N::translate('Widow').'</button>'.
				'<button type="button" id="DEAT_Y_'.    $table_id.'" class="ui-state-default DEAT_Y" title="'.    WT_I18N::translate('Show people who are dead or couples where both partners are deceased.').'">'.WT_I18N::translate('Both dead').'</button>'.
				'<button type="button" id="TREE_R_'.    $table_id.'" class="ui-state-default TREE_R" title="'.    WT_I18N::translate('Show «roots» couples or individuals.  These people may also be called «patriarchs».  They are individuals who have no parents recorded in the database.').'">'.WT_I18N::translate('Roots').'</button>'.
				'<button type="button" id="TREE_L_'.    $table_id.'" class="ui-state-default TREE_L" title="'.    WT_I18N::translate('Show «leaves» couples or individuals.  These are individuals who are alive but have no children recorded in the database.').'">'.WT_I18N::translate('Leaves').'</button>'.
				'<button type="button" id="MARR_U_'.    $table_id.'" class="ui-state-default MARR_U" title="'.    WT_I18N::translate('Show couples with an unknown marriage date.').'">'.WT_Gedcom_Tag::getLabel('MARR').'</button>'.
				'<button type="button" id="MARR_YES_'.  $table_id.'" class="ui-state-default MARR_YES" title="'.  WT_I18N::translate('Show couples who married more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('MARR').'&gt;100</button>'.
				'<button type="button" id="MARR_Y100_'. $table_id.'" class="ui-state-default MARR_Y100" title="'. WT_I18N::translate('Show couples who married within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('MARR').'&lt;=100</button>'.
				'<button type="button" id="MARR_DIV_'.  $table_id.'" class="ui-state-default MARR_DIV" title="'.  WT_I18N::translate('Show divorced couples.').'">'.WT_Gedcom_Tag::getLabel('DIV').'</button>'.
				'<button type="button" id="MULTI_MARR_'.$table_id.'" class="ui-state-default MULTI_MARR" title="'.WT_I18N::translate('Show couples where either partner married more than once.').'">'.WT_I18N::translate('Multiple marriages').'</button>'.
				'<button type="button" id="RESET_'.$table_id.'" class="ui-state-default RESET" title="'.WT_I18N::translate('Reset to the list defaults.').'">'.WT_I18N::translate('Reset').'</button>'
			).'");

			jQuery("div.filtersF_'.$table_id.'").html("'.addslashes(
				'<button type="button" class="ui-state-default" id="cb_parents_'.$table_id.'" onclick="jQuery(\'div.parents_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'.WT_I18N::translate('Show parents').'</button>'.
				'<button type="button" class="ui-state-default" id="charts_fam_list_table" onclick="jQuery(\'div.fam_list_table-charts_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'. WT_I18N::translate('Show statistics charts').'</button>'
			).'");
			
			/* Add event listeners for filtering inputs */
			/* PERSO Modify table to include IsSourced module */		
			jQuery("#MARR_U_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("U", 21);
				jQuery("#MARR_U_'.$table_id.'").addClass("ui-state-active");
				jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#MARR_YES_'.  $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("YES", 21);
				jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_YES_'.$table_id.'").addClass("ui-state-active");
				jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#MARR_Y100_'. $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("Y100", 21);
				jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_Y100_'.$table_id.'").addClass("ui-state-active");
				jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#MARR_DIV_'.  $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("D", 21);
				jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_DIV_'.$table_id.'").addClass("ui-state-active");
				jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#MULTI_MARR_'.$table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("M", 21);
				jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#MULTI_MARR_'.$table_id.'").addClass("ui-state-active");
			});
			jQuery("#DEAT_N_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("N", 22);
				jQuery("#DEAT_N_'.$table_id.'").addClass("ui-state-active");
				jQuery("#DEAT_W_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_H_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#DEAT_W_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("W", 22);
				jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_W_'.$table_id.'").addClass("ui-state-active");
				jQuery("#DEAT_H_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#DEAT_H_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("H", 22);
				jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_W_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_H_'.$table_id.'").addClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#DEAT_Y_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("Y", 22);
				jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_W_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_H_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#DEAT_Y_'.$table_id.'").addClass("ui-state-active");
			});
			jQuery("#TREE_R_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("R", 23);
				jQuery("#TREE_R_'.$table_id.'").addClass("ui-state-active");
				jQuery("#TREE_L_'.$table_id.'").removeClass("ui-state-active");
			});
			jQuery("#TREE_L_'.    $table_id.'").click( function() {
				oTable'.$table_id.'.fnFilter("L", 23);
				jQuery("#TREE_R_'.$table_id.'").removeClass("ui-state-active");
				jQuery("#TREE_L_'.$table_id.'").addClass("ui-state-active");
			});	
			jQuery("#RESET_'.     $table_id.'").click( function() {
				for (i=21; i<=23; i++) {
					oTable'.$table_id.'.fnFilter("", i );
				};
				jQuery("div.filtersH_'.$table_id.' button").removeClass("ui-state-active");
			});

			/* This code is a temporary fix for Datatables bug http://www.datatables.net/forums/discussion/4730/datatables_sort_wrapper-being-added-to-columns-with-bsortable-false/p1*/
			jQuery("th span:eq(9)").css("display", "none");
			jQuery("th div:eq(9)").css("margin", "auto").css("text-align", "center");
			
			jQuery(".fam-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
	');

	$stats = new WT_Stats($GEDCOM);
	$max_age = max($stats->oldestMarriageMaleAge(), $stats->oldestMarriageFemaleAge())+1;

	//-- init chart data
	for ($age=0; $age<=$max_age; $age++) $marr_by_age[$age]='';
	for ($year=1550; $year<2030; $year+=10) $birt_by_decade[$year]='';
	for ($year=1550; $year<2030; $year+=10) $marr_by_decade[$year]='';
	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="fam-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>';
	$html .= '<th>HUSB:GIVN_SURN</th>';
	$html .= '<th>HUSB:SURN_GIVN</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>';
	$html .= '<th>AGE</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>';
	$html .= '<th>WIFE:GIVN_SURN</th>';
	$html .= '<th>WIFE:SURN_GIVN</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>';
	$html .= '<th>AGE</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('MARR'). '</th>';
	$html .= '<th>MARR:DATE</th>';
	$html .= '<th><img src="'. $WT_IMAGES['reminder']. '" alt="'. WT_I18N::translate('Anniversary'). '" title="'. WT_I18N::translate('Anniversary'). '"></th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('PLAC'). '</th>';
	//PERSO Modify table to include IsSourced module
	if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
		$html .= '<th><img src="'.$WT_IMAGES['source'].'" alt="'.WT_I18N::translate('Sourced marriage').'" title="'.WT_I18N::translate('Sourced marriage').'" border="0" /></th>'.
			'<th>SORT_MARRSC</th>';
	} else {
		$html .= '<th>&nbsp;</th><th></th>';
	}
	//END PERSO
	$html .= '<th><img src="'. $WT_IMAGES['children']. '" alt="'. WT_I18N::translate('Children'). '" title="'. WT_I18N::translate('Children'). '"></th>';
	$html .= '<th>NCHI</th>';
	$html .= '<th '.($SHOW_LAST_CHANGE?'':'').'>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th>MARR</th>';
	$html .= '<th>DEAT</th>';
	$html .= '<th>TREE</th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$num = 0;
	$d100y=new WT_Date(date('Y')-100);  // 100 years ago
	foreach ($datalist as $key => $value) {
		if (is_object($value)) { // Array of objects
			$family=$value;
		} elseif (!is_array($value)) { // Array of IDs
			$family=WT_Family::getInstance($value);
		} else { // Array of search results
			$gid = "";
			if (isset($value['gid'])) $gid = $value['gid'];
			if (isset($value['gedcom'])) $family = new WT_Family($value['gedcom']);
			else $family = WT_Family::getInstance($gid);
		}
		if (is_null($family)) continue;
		if ($family->getType() !== 'FAM') continue;
		//PERSO Create decorator for Family
		$dfamily = new WT_Perso_Family($family);
		//END PERSO
		//-- Retrieve husband and wife
		$husb = $family->getHusband();
		if (is_null($husb)) $husb = new WT_Person('');
		$wife = $family->getWife();
		if (is_null($wife)) $wife = new WT_Person('');
		if (!$family->canDisplayDetails()) {
			continue;
		}
		//-- place filtering
		if ($option=='MARR_PLAC' && strstr($family->getMarriagePlace(), $filter)===false) continue;
		$html .= '<tr>';
		//-- Husband name(s)
		$html .= '<td colspan="2">';
		foreach ($husb->getAllNames() as $num=>$name) {
			if ($name['type']=='NAME') {
				$title='';
			} else {
				$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $husb)).'"';
			}
			if ($num==$husb->getPrimaryName()) {
				$class=' class="name2"';
				$sex_image=$husb->getSexImage();
				list($surn, $givn)=explode(',', $name['sort']);
			} else {
				$class='';
				$sex_image='';
			}
			// Only show married names if they are the name we are filtering by.
			if ($name['type']!='_MARNM' || $num==$husb->getPrimaryName()) {
				//PERSO Add Sosa Icon
				$dhusb = new WT_Perso_Person($husb);
				$html .= '<a '. $title. ' href="'. $family->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dhusb->getSosaNumbers(), 1, 'smaller'). '<br>';
				//END PERSO
			}
		}
		// Husband parents
		$html .= $husb->getPrimaryParentsNames('parents_'.$table_id.' details1', 'none');
		$html .= '</td>';
		// Dummy column to match colspan in header
		$html .= '<td style="display:none;"></td>';
		//-- Husb GIVN
		// Use "AAAA" as a separator (instead of ",") as JavaScript.localeCompare() ignores
		// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
		// Similarly, @N.N. would sort as NN.
		$html .= '<td>'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
		$html .= '<td>'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
		$mdate=$family->getMarriageDate();
		//-- Husband age
		$hdate=$husb->getBirthDate();
		if ($hdate->isOK() && $mdate->isOK()) {
			if ($hdate->gregorianYear()>=1550 && $hdate->gregorianYear()<2030) {
				$birt_by_decade[floor($hdate->gregorianYear()/10)*10] .= $husb->getSex();
			}
			$hage=WT_Date::getAge($hdate, $mdate, 0);
			if ($hage>=0 && $hage<=$max_age) {
				$marr_by_age[$hage].=$husb->getSex();
			}
		}
		$html .= '<td>'.WT_Date::getAge($hdate, $mdate, 2).'</td><td>'.WT_Date::getAge($hdate, $mdate, 1).'</td>';
		//-- Wife name(s)
		$html .= '<td colspan="2">';
		foreach ($wife->getAllNames() as $num=>$name) {
			if ($name['type']=='NAME') {
				$title='';
			} else {
				$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $wife)).'"';
			}
			if ($num==$wife->getPrimaryName()) {
				$class=' class="name2"';
				$sex_image=$wife->getSexImage();
				list($surn, $givn)=explode(',', $name['sort']);
			} else {
				$class='';
				$sex_image='';
			}
			// Only show married names if they are the name we are filtering by.
			if ($name['type']!='_MARNM' || $num==$wife->getPrimaryName()) {
				//PERSO Add Sosa Icon
				$dwife = new WT_Perso_Person($wife);
				$html .= '<a '. $title. ' href="'. $family->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dwife->getSosaNumbers(), 1, 'smaller'). '<br>';
				//END PERSO
			}
		}
		// Wife parents
		$html .= $wife->getPrimaryParentsNames("parents_".$table_id." details1", 'none');
		$html .= '</td>';
		// Dummy column to match colspan in header
		$html .= '<td style="display:none;"></td>';
		//-- Wife GIVN
		//-- Husb GIVN
		// Use "AAAA" as a separator (instead of ",") as JavaScript.localeCompare() ignores
		// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
		// Similarly, @N.N. would sort as NN.
		$html .= '<td>'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
		$html .= '<td>'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
		$mdate=$family->getMarriageDate();
		//-- Wife age
		$wdate=$wife->getBirthDate();
		if ($wdate->isOK() && $mdate->isOK()) {
			if ($wdate->gregorianYear()>=1550 && $wdate->gregorianYear()<2030) {
				$birt_by_decade[floor($wdate->gregorianYear()/10)*10] .= $wife->getSex();
			}
			$wage=WT_Date::getAge($wdate, $mdate, 0);
			if ($wage>=0 && $wage<=$max_age) {
				$marr_by_age[$wage].=$wife->getSex();
			}
		}
		$html .= '<td>'.WT_Date::getAge($wdate, $mdate, 2).'</td><td>'.WT_Date::getAge($wdate, $mdate, 1).'</td>';
		//-- Marriage date
		$html .= '<td>';
		if ($marriage_dates=$family->getAllMarriageDates()) {
			foreach ($marriage_dates as $n=>$marriage_date) {
				if ($n) {
					$html .= '<br>';
				}
				$html .= '<div>'. $marriage_date->Display(!$SEARCH_SPIDER). '</div>';
			}
			if ($marriage_dates[0]->gregorianYear()>=1550 && $marriage_dates[0]->gregorianYear()<2030) {
				$marr_by_decade[floor($marriage_dates[0]->gregorianYear()/10)*10] .= $husb->getSex().$wife->getSex();
			}
		} else if (get_sub_record(1, '1 _NMR', $family->getGedcomRecord())) {
			$hus = $family->getHusband();
			$wif = $family->getWife();
			if (empty($wif) && !empty($hus)) $html .= WT_Gedcom_Tag::getLabel('_NMR', $hus);
			else if (empty($hus) && !empty($wif)) $html .= WT_Gedcom_Tag::getLabel('_NMR', $wif);
			else $html .= WT_Gedcom_Tag::getLabel('_NMR');
		} else if (get_sub_record(1, '1 _NMAR', $family->getGedcomRecord())) {
			$hus = $family->getHusband();
			$wif = $family->getWife();
			if (empty($wif) && !empty($hus)) $html .= WT_Gedcom_Tag::getLabel('_NMAR', $hus);
			else if (empty($hus) && !empty($wif)) $html .= WT_Gedcom_Tag::getLabel('_NMAR', $wif);
			else $html .= WT_Gedcom_Tag::getLabel('_NMAR');
		} else {
			$factdetail = explode(' ', trim($family->getMarriageRecord()));
			if (isset($factdetail)) {
				if (count($factdetail) >= 3) {
					if (strtoupper($factdetail[2]) != "N") {
						$html .= WT_I18N::translate('yes');
					} else {
						$html .= WT_I18N::translate('no');
					}
				} else {
					$html .= '&nbsp;';
				}
			}
		}
		$html .= '</td>';
		//-- Event date (sortable)hidden by datatables code
		$html .= '<td>';
		if ($marriage_dates) {
			$html .= $marriage_date->JD();
		} else {
			$html .= 0;
		}
		$html .= '</td>';
		//-- Marriage anniversary
		$html .= '<td>'.WT_Date::getAge($mdate, null, 2).'</td>';
		//-- Marriage place
		$html .= '<td>';
		foreach ($family->getAllMarriagePlaces() as $n=>$marriage_place) {
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= get_place_short($marriage_place);
			} else {
				$html .= '<a href="'. get_place_url($marriage_place). '" title="'. $marriage_place. '">';
				$html .= highlight_search_hits(get_place_short($marriage_place)). '</a>';
			}
		}
		$html .= '</td>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$isMSourced = $dfamily->isMarriageSourced();
			$html .= '<td>'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $isMSourced, 'MARR', 1, 'medium').'</td>'.
				'<td>'.$isMSourced.'</td>';
		} else {
			$html .= '<td>&nbsp;</td>'.
				'<td></td>';
		}
		//END PERSO
		//-- Number of children
		$nchi=$family->getNumberOfChildren();
		$html .= '<td>'. WT_I18N::number($nchi). '</td><td>'. $nchi. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $family->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Sorting by marriage date
		$html .= '<td>';
		if (!$family->canDisplayDetails() || !$mdate->isOK()) {
			$html .= 'U';
		} else {
			if (WT_Date::Compare($mdate, $d100y)>0) {
				$html .= 'Y100';
			} else {
				$html .= 'YES';
			}
		}
		if ($family->isDivorced()) {
			$html .= 'D';
		}
		if (count($husb->getSpouseFamilies())>1 || count($wife->getSpouseFamilies())>1) {
			$html .= 'M';
		}
		$html .= '</td>';
		//-- Sorting alive/dead
		$html .= '<td>';
			if ($husb->isDead() && $wife->isDead()) $html .= 'Y';
			if ($husb->isDead() && !$wife->isDead()) {
				if ($wife->getSex()=='F') $html .= 'H';
				if ($wife->getSex()=='M') $html .= 'W'; // male partners
			}
			if (!$husb->isDead() && $wife->isDead()) {
				if ($husb->getSex()=='M') $html .= 'W';
				if ($husb->getSex()=='F') $html .= 'H'; // female partners
			}
			if (!$husb->isDead() && !$wife->isDead()) $html .= 'N';
		$html .= '</td>';
		//-- Roots or Leaves
		$html .= '<td>';
			if (!$husb->getChildFamilies() && !$wife->getChildFamilies()) { $html .= 'R'; } // roots
			elseif (!$husb->isDead() && !$wife->isDead() && $family->getNumberOfChildren()<1) { $html .= 'L'; } // leaves
			else { $html .= '&nbsp;'; }
		$html .= '</td>
		</tr>';
	}
	$html .= '</tbody>'.
		'</table>';
	//-- charts
	$html .= '<div class="fam_list_table-charts_'. $table_id. '" style="display:none">
		<table class="list-charts"><tr><td>'.
		print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')).
		'</td><td>'.
		print_chart_by_decade($marr_by_decade, WT_I18N::translate('Decade of marriage')).
		'</td></tr><tr><td colspan="2">'.
		print_chart_by_age($marr_by_age, WT_I18N::translate('Age in year of marriage')).
		'</td></tr></table>
		</div>
		</div>'; // Close "fam-list"
	
	return $html;
}

// print a table of sources
function format_sour_table($datalist) {
	global $SHOW_LAST_CHANGE, $WT_IMAGES, $controller;
	$html = '';
	$table_id = "ID".floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#'.$table_id.'").dataTable( {
				"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
				'.WT_I18N::datatablesI18N().',
				"bJQueryUI": true,
				"bAutoWidth":false,
				"bProcessing": true,
				"aoColumns": [
					/*  0 title  */ {"iDataSort": 1},
					/*  1 TITL   */ {"bVisible": false, "sType": "unicode"},
					/*  2 author */ {"sType": "unicode"},
					/*  3 #indi  */ {"iDataSort": 4, "sClass": "center"},
					/*  4 #INDI  */ {"sType": "numeric", "bVisible": false},
					/*  5 #fam   */ {"iDataSort": 6, "sClass": "center"},
					/*  6 #FAM   */ {"sType": "numeric", "bVisible": false},
					/*  7 #obje  */ {"iDataSort": 8, "sClass": "center"},
					/*  8 #OBJE  */ {"sType": "numeric", "bVisible": false},
					/*  9 #note  */ {"iDataSort": 10, "sClass": "center"},
					/* 10 #NOTE  */ {"sType": "numeric", "bVisible": false},
					/* 11 CHAN   */ {"bVisible": '.($SHOW_LAST_CHANGE?'true':'false').'},
					/* 12 DELETE */ {"bVisible": '.(WT_USER_GEDCOM_ADMIN?'true':'false').', "bSortable": false}
				],
				"iDisplayLength": 20,
				"sPaginationType": "full_numbers"
		   });
			jQuery(".source-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');

	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="source-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('TITL'). '</th>';
	$html .= '<th>TITL</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('AUTH'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Individuals'). '</th>';
	$html .= '<th>#INDI</th>';
	$html .= '<th>'. WT_I18N::translate('Families'). '</th>';
	$html .= '<th>#FAM</th>';
	$html .= '<th>'. WT_I18N::translate('Media objects'). '</th>';
	$html .= '<th>#OBJE</th>';
	$html .= '<th>'. WT_I18N::translate('Shared notes'). '</th>';
	$html .= '<th>#NOTE</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th>&nbsp;</th>';//delete
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$n=0;
	foreach ($datalist as $key=>$value) {
		if (is_object($value)) { // Array of objects
			$source=$value;
		} elseif (!is_array($value)) { // Array of IDs
			$source=WT_Source::getInstance($key); // from placelist
			if (is_null($source)) {
				$source=WT_Source::getInstance($value);
			}
			unset($value);
		} else { // Array of search results
			$gid='';
			if (isset($value['gid'])) {
				$gid=$value['gid'];
			}
			if (isset($value['gedcom'])) {
				$source=new WT_Source($value['gedcom']);
			} else {
				$source=WT_Source::getInstance($gid);
			}
		}
		if (!$source || !$source->canDisplayDetails()) {
			continue;
		}
		$html .= '<tr>';
		//-- Source name(s)
		$html .= '<td>';
		foreach ($source->getAllNames() as $n=>$name) {
			if ($n) {
				$html .= '<br>';
			}
			if ($n==$source->getPrimaryName()) {
				$html .= '<a class="name2" href="'. $source->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			} else {
				$html .= '<a href="'. $source->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			}
		}	
		$html .= '</td>';
		// Sortable name
		$html .= '<td>'. strip_tags($source->getFullName()). '</td>';
		//-- Author
		$html .= '<td>'. highlight_search_hits(htmlspecialchars($source->getAuth())). '</td>';
		//-- Linked INDIs
		$num=$source->countLinkedIndividuals();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked FAMs
		$num=$source->countLinkedfamilies();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked OBJEcts
		$num=$source->countLinkedMedia();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked NOTEs
		$num=$source->countLinkedNotes();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $source->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Delete 
		if (WT_USER_GEDCOM_ADMIN) {
			$html .= '<td><div title="'. WT_I18N::translate('Delete'). '" class="deleteicon" onclick="if (confirm(\''. addslashes(WT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($source->getFullName()))). '\')) jQuery.post(\'action.php\',{action:\'delete-source\',xref:\''. $source->getXref(). '\'},function(){location.reload();})"><span class="link_text">'. WT_I18N::translate('Delete'). '</span></div></td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';
		
	return $html;
}

// print a table of shared notes
function format_note_table($datalist) {
	global $SHOW_LAST_CHANGE, $WT_IMAGES, $controller;
	$html = '';
	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#'.$table_id.'").dataTable({
			"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			'.WT_I18N::datatablesI18N().',
			"bJQueryUI": true,
			"bAutoWidth":false,
			"bProcessing": true,
			"aoColumns": [
				/*  0 title  */ {"sType": "unicode"},
				/*  1 #indi  */ {"iDataSort": 2, "sClass": "center"},
				/*  2 #INDI  */ {"sType": "numeric", "bVisible": false},
				/*  3 #fam   */ {"iDataSort": 4, "sClass": "center"},
				/*  4 #FAM   */ {"sType": "numeric", "bVisible": false},
				/*  5 #obje  */ {"iDataSort": 6, "sClass": "center"},
				/*  6 #OBJE  */ {"sType": "numeric", "bVisible": false},
				/*  7 #sour  */ {"iDataSort": 8, "sClass": "center"},
				/*  8 #SOUR  */ {"sType": "numeric", "bVisible": false},
				/*  9 CHAN   */ {"bVisible": '.($SHOW_LAST_CHANGE?'true':'false').'},
				/* 10 DELETE */ {"bVisible": '.(WT_USER_GEDCOM_ADMIN?'true':'false').', "bSortable": false}
			],
			"iDisplayLength": 20,
			"sPaginationType": "full_numbers"
	   });
			jQuery(".note-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');
		
	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="note-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('TITL'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Individuals'). '</th>';
	$html .= '<th>#INDI</th>';
	$html .= '<th>'. WT_I18N::translate('Families'). '</th>';
	$html .= '<th>#FAM</th>';
	$html .= '<th>'. WT_I18N::translate('Media objects'). '</th>';
	$html .= '<th>#OBJE</th>';
	$html .= '<th>'. WT_I18N::translate('Sources'). '</th>';
	$html .= '<th>#SOUR</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th>&nbsp;</th>';//delete
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	foreach ($datalist as $note) {
		if (!$note->canDisplayDetails()) {
			continue;
		}
		$html .= '<tr>';
		//-- Shared Note name
		$html .= '<td><a class="name2" href="'. $note->getHtmlUrl(). '">'. highlight_search_hits($note->getFullName()). '</a></td>';
		//-- Linked INDIs
		$num=$note->countLinkedIndividuals();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked FAMs
		$num=$note->countLinkedfamilies();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked OBJEcts
		$num=$note->countLinkedMedia();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked SOURs
		$num=$note->countLinkedSources();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $note->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td></td>';
		}
		//-- Delete 
		if (WT_USER_GEDCOM_ADMIN) {
			$html .= '<td><div title="'. WT_I18N::translate('Delete'). '" class="deleteicon" onclick="if (confirm(\''. addslashes(WT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($note->getFullName()))). '\')) jQuery.post(\'action.php\',{action:\'delete-note\',xref:\''. $note->getXref(). '\'},function(){location.reload();})"><span class="link_text">'. WT_I18N::translate('Delete'). '</span></div></td>';
		} else {
			$html .= '<td></td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';
		
	return $html;
}

// print a table of repositories
function format_repo_table($repos) {
	global $SHOW_LAST_CHANGE, $WT_IMAGES, $SEARCH_SPIDER, $controller;
	$html = '';
	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#'.$table_id.'").dataTable({
			"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			'.WT_I18N::datatablesI18N().',
			"bJQueryUI": true,
			"bAutoWidth":false,
			"bProcessing": true,
			"aoColumns": [
				/* 0 name   */ {"sType": "unicode"},
				/* 1 #sour  */ {"iDataSort": 2, "sClass": "center"},
				/* 2 #SOUR  */ {"sType": "numeric", "bVisible": false},
				/* 3 CHAN   */ {"bVisible": '.($SHOW_LAST_CHANGE?'true':'false').'},
				/* 4 DELETE */ {"bVisible": '.(WT_USER_GEDCOM_ADMIN?'true':'false').', "bSortable": false}
			],
			"iDisplayLength": 20,
			"sPaginationType": "full_numbers"
	   });
		jQuery(".repo-list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
		');
		
	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="repo-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_I18N::translate('Repository name'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Sources'). '</th>';
	$html .= '<th>#SOUR</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th>&nbsp;</th>';//delete
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$n=0;
	foreach ($repos as $repo) {
		if (!$repo->canDisplayDetails()) {
			continue;
		}
		$html .= '<tr>';
		//-- Repository name(s)
		$html .= '<td>';
		foreach ($repo->getAllNames() as $n=>$name) {
			if ($n) {
				$html .= '<br>';
			}
			if ($n==$repo->getPrimaryName()) {
				$html .= '<a class="name2" href="'. $repo->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			} else {
				$html .= '<a href="'. $repo->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			}
		}	
		$html .= '</td>';
		//-- Linked SOURces
		$num=$repo->countLinkedSources();
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $repo->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Delete 
		if (WT_USER_GEDCOM_ADMIN) {
			$html .= '<td><div title="'. WT_I18N::translate('Delete'). '" class="deleteicon" onclick="if (confirm(\''. addslashes(WT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($repo->getFullName()))). '\')) jQuery.post(\'action.php\',{action:\'delete-repository\',xref:\''. $repo->getXref(). '\'},function(){location.reload();})"><span class="link_text">'. WT_I18N::translate('Delete'). '</span></div></td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';
	
	return $html;
}

// print a table of media objects
function format_media_table($datalist) {
	global $SHOW_LAST_CHANGE, $WT_IMAGES, $controller;
	$html = '';
	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#'.$table_id.'").dataTable({
			"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			'.WT_I18N::datatablesI18N().',
			"bJQueryUI": true,
			"bAutoWidth":false,
			"bProcessing": true,
			"aoColumns": [
				/* 0 media  */ {"bSortable": false},
				/* 1 title  */ {"sType": "unicode"},
				/* 2 #indi  */ {"iDataSort": 3, "sClass": "center"},
				/* 3 #INDI  */ {"sType": "numeric", "bVisible": false},
				/* 4 #fam   */ {"iDataSort": 5, "sClass": "center"},
				/* 5 #FAM   */ {"sType": "numeric", "bVisible": false},
				/* 6 #sour  */ {"iDataSort": 7, "sClass": "center"},
				/* 7 #SOUR  */ {"sType": "numeric", "bVisible": false},
				/* 8 CHAN   */ {"bVisible": '.($SHOW_LAST_CHANGE?'true':'false').'},
			],
			"iDisplayLength": 20,
			"sPaginationType": "full_numbers"
	   });
		jQuery(".media-list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
		');
		
	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="media-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_I18N::translate('Media'). '</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('TITL'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Individuals'). '</th>';
	$html .= '<th>#INDI</th>';
	$html .= '<th>'. WT_I18N::translate('Families'). '</th>';
	$html .= '<th>#FAM</th>';
	$html .= '<th>'. WT_I18N::translate('Sources'). '</th>';
	$html .= '<th>#SOUR</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$n = 0;
	foreach ($datalist as $key => $value) {
		if (is_object($value)) { // Array of objects
			$media=$value;
		} else {
			$media = new WT_Media($value["GEDCOM"]);
			if (is_null($media)) $media = WT_Media::getInstance($key);
			if (is_null($media)) continue;
		}
		if ($media->canDisplayDetails()) {
			$name = $media->getFullName();
			$html .= "<tr>";
			//-- Object thumbnail
			$html .= '<td><img src="'. $media->getThumbnail(). '" alt="'. htmlspecialchars(strip_tags($name)). '"></td>';
			//-- Object name(s)
			$html .= '<td>';
			$html .= '<a href="'. $media->getHtmlUrl(). '" class="list_item name2">';
			$html .= highlight_search_hits($name). '</a>';
			if (WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT)
				$html .= '<br><a href="'. $media->getHtmlUrl(). '">'. basename($media->getFilename()). '</a>';
			if ($media->getNote()) $html .= '<br>'. print_fact_notes('1 NOTE ', $media->getNote(), 1);
			$html .= '</td>';

			//-- Linked INDIs
			$num=$media->countLinkedIndividuals();
			$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
			//-- Linked FAMs
			$num=$media->countLinkedfamilies();
			$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
			//-- Linked SOURces
			$num=$media->countLinkedSources();
			$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
			//-- Last change
			if ($SHOW_LAST_CHANGE) {
				$html .= '<td>'. $media->LastChangeTimestamp(). '</td>';
			} else {
				$html .= '<td>&nbsp;</td>';
			}
			$html .= '</tr>';
		}
	}
	$html .= '</tbody></table></div>';
	
	return $html;
}

// Print a table of surnames, for the top surnames block, the indi/fam lists, etc.
// $surnames - array (of SURN, of array of SPFX_SURN, of array of PID)
// $type     - "indilist.php" (counts of individuals) or "famlist.php" (counts of spouses)
// PERSO Add $extra parameter for more complex urls
// $extra    - Extra URL which should be added to the returned link
function format_surname_table($surnames, $script, $extra = '') {
	global $controller;
	$html = '';
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["num-asc" ]=function(a,b) {a=parseFloat(a); b=parseFloat(b); return (a<b) ? -1 : (a>b ? 1 : 0);};
			jQuery.fn.dataTableExt.oSort["num-desc"]=function(a,b) {a=parseFloat(a); b=parseFloat(b); return (a>b) ? -1 : (a<b ? 1 : 0);};
			jQuery(".surname-list").dataTable( {
			"sDom": \'t\',
			"bJQueryUI": true,
			"bAutoWidth":false,
			"bPaginate": false,
			"aaSorting": [],
			"aoColumns": [
				/*  0 name  */ {iDataSort:1},
				/*  1 NAME  */ {bVisible:false, sType:"num"},
				/*  2 count */ {iDataSort:3, sClass:"center"},
				/*  3 COUNT */ {bVisible:false}
			],
			});
		');

	if ($script=='famlist.php') {
		$col_heading=WT_I18N::translate('Spouses');
	} else {
		$col_heading=WT_I18N::translate('Individuals');
	}

	$html .= '<table class="surname-list">'.
		'<thead><tr>'.
		'<th>'.WT_Gedcom_Tag::getLabel('SURN').'</th>'.
		'<th>&nbsp;</th>'.
		'<th>'.$col_heading.'</th>'.
		'<th>&nbsp;</th>'.
		'</tr></thead>';

	$n=0; // We have already sorted the data - use this as a surrogate sort key
	$html .= '<tbody>';
	foreach ($surnames as $surn=>$surns) {
		// Each surname links back to the indi/fam surname list
		//PERSO Add $extra parameter for more complex urls
		if ($surn) {
			$url=$script.'?surname='.rawurlencode($surn).'&amp;ged='.WT_GEDURL.$extra;
		} else {
			$url=$script.'?alpha=,&amp;ged='.WT_GEDURL.$extra;
		}
		//END PERSO
		// Row counter
		$html.='<tr>';
		// Surname
		$html.='<td>';
		// Multiple surname variants, e.g. von Groot, van Groot, van der Groot, etc.
		foreach ($surns as $spfxsurn=>$indis) {
			if ($spfxsurn) {
				$html.='<a href="'.$url.'">'.htmlspecialchars($spfxsurn).'</a><br>';
			} else {
				// No surname, but a value from "2 SURN"?  A common workaround for toponyms, etc.
				$html.='<a href="'.$url.'">'.htmlspecialchars($surn).'</a><br>';
			}
		}
		$html.='</td>';
		// Surrogate sort column for name
		$html.='<td>'.$n++.'</td>';
		// Surname count
		$html.='<td>';
		$subtotal=0;
		foreach ($surns as $spfxsurn=>$indis) {
			$subtotal+=count($indis);
			$html.=WT_I18N::number(count($indis)).'<br>';
		}
		// More than one surname variant? Show a subtotal
		if (count($surns)>1) {
			$html.=WT_I18N::number($subtotal);
		}
		$html.='</td>';
		// add hidden numeric sort column
		$html.='<td>'. $subtotal. '</td></tr>';
	}
	$html .= '</tbody></table>';
	
	return $html;
}

// Print a tagcloud of surnames.
// @param $surnames array (of SURN, of array of SPFX_SURN, of array of PID)
// @param $type string, indilist or famlist
// @param $totals, boolean, show totals after each name
// PERSO Add $extra parameter for more complex urls
// @param $extra string Extra URL which should be added to the returned link
function format_surname_tagcloud($surnames, $script, $totals, $extra ='') {
	$cloud=new Zend_Tag_Cloud(
		array(
			'tagDecorator'=>array(
				'decorator'=>'HtmlTag',
				'options'=>array(
					'htmlTags'=>array(),
					'fontSizeUnit'=>'%',
					'minFontSize'=>80,
					'maxFontSize'=>250
				)
			),
			'cloudDecorator'=>array(
				'decorator'=>'HtmlCloud',
				'options'=>array(
					'htmlTags'=>array(
						'div'=>array(
							'class'=>'tag_cloud'
						)
					)
				)
			)
		)
	);
	foreach ($surnames as $surn=>$surns) {
		foreach ($surns as $spfxsurn=>$indis) {
			$cloud->appendTag(array(
				'title'=>$totals ? WT_I18N::translate('%1$s (%2$d)', $spfxsurn, count($indis)) : $spfxsurn,
				'weight'=>count($indis),
				'params'=>array(
					'url'=>$surn ?
						//PERSO Add $extra parameter for more complex urls
						$script.'?surname='.urlencode($surn).'&amp;ged='.WT_GEDURL.$extra :
						$script.'?alpha=,&amp;ged='.WT_GEDURL.$extra
						//END PERSO
				)
			));
		}
	}
	return (string)$cloud;
}

// Print a list of surnames.
// @param $surnames array (of SURN, of array of SPFX_SURN, of array of PID)
// @param $style, 1=bullet list, 2=semicolon-separated list, 3=tabulated list with up to 4 columns
// @param $totals, boolean, show totals after each name
// @param $type string, indilist or famlist
// PERSO Add $extra parameter for more complex urls
// @param $extra string Extra URL which should be added to the returned link
function format_surname_list($surnames, $style, $totals, $script, $extra = '') {
	global $GEDCOM;

	$html=array();
	foreach ($surnames as $surn=>$surns) {
		// Each surname links back to the indilist
		//PERSO Add $extra parameter for more complex urls
		if ($surn) {
			$url=$script.'?surname='.urlencode($surn).'&amp;ged='.rawurlencode($GEDCOM).$extra;
		} else {
			$url=$script.'?alpha=,&amp;ged='.rawurlencode($GEDCOM).$extra;
		}
		//END PERSO
		// If all the surnames are just case variants, then merge them into one
		// Comment out this block if you want SMITH listed separately from Smith
		$first_spfxsurn=null;
		foreach ($surns as $spfxsurn=>$indis) {
			if ($first_spfxsurn) {
				if (utf8_strtoupper($spfxsurn)==utf8_strtoupper($first_spfxsurn)) {
					$surns[$first_spfxsurn]=array_merge($surns[$first_spfxsurn], $surns[$spfxsurn]);
					unset ($surns[$spfxsurn]);
				}
			} else {
				$first_spfxsurn=$spfxsurn;
			}
		}
		$subhtml='<a href="'.$url.'">'.htmlspecialchars(implode(WT_I18N::$list_separator, array_keys($surns))).'</a>';

		if ($totals) {
			$subtotal=0;
			foreach ($surns as $spfxsurn=>$indis) {
				$subtotal+=count($indis);
			}
			$subhtml.='&nbsp;('.$subtotal.')';
		}
		$html[]=$subhtml;

	}
	switch ($style) {
	case 1:
		return '<ul><li>'.implode('</li><li>', $html).'</li></ul>';
	case 2:
		return implode('; ', $html);
	case 3:
		$i = 0;
		$count = count($html);
		$count_indi = 0;
		$col = 1;
		if ($count>36) $col=4;
		else if ($count>18) $col=3;
		else if ($count>6) $col=2;
		$newcol=ceil($count/$col);
		$html2 ='<table class="list_table"><tr>';
		$html2.='<td class="list_value" style="padding: 14px;">';

		foreach ($html as $surn=>$surns) {
			$html2.= $surns.'<br>';
			$i++;
			if ($i==$newcol && $i<$count) {
				$html2.='</td><td class="list_value" style="padding: 14px;">';
				$newcol=$i+ceil($count/$col);
			}
		}
		$html2.='</td></tr></table>';

		return $html2;
	}
}


// print a list of recent changes
function print_changes_list($change_ids, $sort) {
	$n = 0;
	$arr=array();
	foreach ($change_ids as $change_id) {
		$record = WT_GedcomRecord::getInstance($change_id);
		if (!$record || !$record->canDisplayDetails()) {
			continue;
		}
		// setup sorting parameters
		$arr[$n]['record'] = $record;
		$arr[$n]['jd'] = ($sort == 'name') ? 1 : $n;
		$arr[$n]['anniv'] = $record->LastChangeTimestamp(true);
		$arr[$n++]['fact'] = $record->getSortName(); // in case two changes have same timestamp
	}

	switch ($sort) {
	case 'name':
		uasort($arr, 'event_sort_name');
		break;
	case 'date_asc':
		uasort($arr, 'event_sort');
		$arr = array_reverse($arr);
		break;
	case 'date_desc':
		uasort($arr, 'event_sort');
	}
	$html = '';
	foreach ($arr as $value) {
		$html .= '<a href="' . $value['record']->getHtmlUrl() . '" class="list_item name2">' . $value['record']->getFullName() . '</a>';
		$html .= '<div class="indent" style="margin-bottom:5px">';
		if ($value['record']->getType() == 'INDI') {
			if ($value['record']->getAddName()) {
				$html .= '<a href="' . $value['record']->getHtmlUrl() . '" class="list_item">' . $value['record']->getAddName() . '</a>';
			}
		}
		$html .= /* I18N: [a record was] Changed on <date/time> by <user> */ WT_I18N::translate('Changed on %1$s by %2$s', $value['record']->LastChangeTimestamp(), $value['record']->LastChangeUser());
		$html .= '</div>';
	}
	return $html;
}

// print a table of recent changes
function print_changes_table($change_ids, $sort) {
	global $WT_IMAGES, $controller;

	$return = '';
	$n = 0;
	$table_id = "ID" . floor(microtime() * 1000000); // create a unique ID
	switch ($sort) {
	case 'name':        //name
		$aaSorting = "[5,'asc'], [4,'desc']";
		break;
	case 'date_asc':    //date ascending
		$aaSorting = "[4,'asc'], [5,'asc']";
		break;
	case 'date_desc':   //date descending
		$aaSorting = "[4,'desc'], [5,'asc']";
		break;
	}
	$html = '';
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#'.$table_id.'").dataTable({
				"sDom": \'t\',
				"bPaginate": false,
				"bAutoWidth":false,
				"bLengthChange": false,
				"bFilter": false,
				'.WT_I18N::datatablesI18N().',
				"bJQueryUI": true,
				"aaSorting": ['.$aaSorting.'],
				"aoColumns": [
					/* 0-Type */    {"bSortable": false, "sClass": "center"},
					/* 1-Record */  {"iDataSort": 5},
					/* 2-Change */  {"iDataSort": 4},
					/* 3-By */      null,
					/* 4-DATE */    {"bVisible": false},
					/* 5-SORTNAME */{"sType": "unicode", "bVisible": false}
				]
			});
		');

		//-- table header
		$html .= "<table id='" . $table_id . "' class='width100'>";
		$html .= "<thead><tr>";
		$html .= "<th>&nbsp;</th>";
		$html .= "<th>" . WT_I18N::translate('Record') . "</th>";
		$html .= "<th>" . WT_Gedcom_Tag::getLabel('CHAN') . "</th>";
		$html .= "<th>" . WT_Gedcom_Tag::getLabel('_WT_USER') . "</th>";
		$html .= "<th>DATE</th>";     //hidden by datatables code
		$html .= "<th>SORTNAME</th>"; //hidden by datatables code
		$html .= "</tr></thead><tbody>";

		//-- table body
		foreach ($change_ids as $change_id) {
		$record = WT_GedcomRecord::getInstance($change_id);
		if (!$record || !$record->canDisplayDetails()) {
			continue;
		}
		$html .= '<tr><td>';
		$indi = false;
		switch ($record->getType()) {
			case "INDI":
				$icon = $record->getSexImage('small', '', '', false);
				$indi = true;
				break;
			case "FAM":
				$icon = '<img src="' . $WT_IMAGES['cfamily'] . '" title="" alt="" height="12">';
				break;
			case "OBJE":
				$icon = '<img src="' . $record->getMediaIcon() . '" title="" alt="" height="12">';
				break;
			case "NOTE":
				$icon = '<img src="' . $WT_IMAGES['note'] . '" title="" alt="" height="12">';
				break;
			case "SOUR":
				$icon = '<img src="' . $WT_IMAGES['source'] . '" title="" alt="" height="12">';
				break;
			case "REPO":
				$icon = '<img src="' . $WT_IMAGES['repository'] . '" title="" alt="" height="12">';
				break;
			default:
				$icon = '&nbsp;';
				break;
		}
		$html .= '<a href="'. $record->getHtmlUrl() .'">'. $icon . '</a>';
		$html .= '</td>';
		++$n;
		//-- Record name(s)
		$name = $record->getFullName();
		$html .= '<td class="wrap">';
		$html .= '<a href="'. $record->getHtmlUrl() .'">'. $name . '</a>';
		if ($indi) {
			$addname = $record->getAddName();
			if ($addname) {
				$html .= '<div class="indent"><a href="'. $record->getHtmlUrl() .'">'. $addname . '</a></div>';
			}
		}
		$html .= "</td>";
		//-- Last change date/time
		$html .= "<td class='wrap'>" . $record->LastChangeTimestamp() . "</td>";
		//-- Last change user
		$html .= "<td class='wrap'>" . $record->LastChangeUser() . "</td>";
		//-- change date (sortable) hidden by datatables code
		$html .= "<td>" . $record->LastChangeTimestamp(true) . "</td>";
		//-- names (sortable) hidden by datatables code
		$html .= "<td>" . $record->getSortName() . "</td></tr>";
	}

	$html .= '</tbody></table>';
	return $html;
}


// print a table of events
function print_events_table($startjd, $endjd, $events='BIRT MARR DEAT', $only_living=false, $sort_by='anniv') {
	global $WT_IMAGES, $controller;
	$html = '';
	$table_id = "ID".floor(microtime()*1000000); // each table requires a unique ID
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
			jQuery("#'.$table_id.'").dataTable({
				"sDom": \'t\',
				'.WT_I18N::datatablesI18N().',
				"bAutoWidth":false,
				"bPaginate": false,
				"bLengthChange": false,
				"bFilter": false,
				"bInfo": true,
				"bJQueryUI": true,
				"aaSorting": [[ '.($sort_by=='alpha' ? 1 : 3).', "asc"]],
				"aoColumns": [
					/* 0-Record */ { "iDataSort": 1},
					/* 1-NAME */   { "bVisible": false },
					/* 2-Date */   { "iDataSort": 3 },
					/* 3-DATE */   { "bVisible": false },
					/* 4-Anniv. */ { "iDataSort": 5, "sClass": "center"},
					/* 5-ANNIV  */ { "sType": "numeric", "bVisible": false},
					/* 6-Event */  { "sClass": "center" }
				]
			});		
		');

	// Did we have any output?  Did we skip anything?
	$output = 0;
	$filter = 0;
	$filtered_events = array();

	foreach (get_events_list($startjd, $endjd, $events) as $value) {
		$record=$value['record'];
		//-- only living people ?
		if ($only_living) {
			if ($record->getType()=="INDI" && $record->isDead()) {
				$filter ++;
				continue;
			}
			if ($record->getType()=="FAM") {
				$husb = $record->getHusband();
				if (is_null($husb) || $husb->isDead()) {
					$filter ++;
					continue;
				}
				$wife = $record->getWife();
				if (is_null($wife) || $wife->isDead()) {
					$filter ++;
					continue;
				}
			}
		}

		// Privacy
		if (!$record->canDisplayDetails() || !canDisplayFact($record->getXref(), $record->getGedId(), $value['factrec'])) {
			continue;
		}
		//-- Counter
		$output ++;

		if ($output==1) {
			//-- table body
			$html .= '<table id="'.$table_id.'" class="width100">';
			$html .= '<thead><tr>';
			$html .= '<th>'.WT_I18N::translate('Record').'</th>';
			$html .= '<th>NAME</th>'; //hidden by datatables code
			$html .= '<th>'.WT_Gedcom_Tag::getLabel('DATE').'</th>';
			$html .= '<th>DATE</th>'; //hidden by datatables code
			$html .= '<th><img src="'.$WT_IMAGES["reminder"].'" alt="'.WT_I18N::translate('Anniversary').'" title="'.WT_I18N::translate('Anniversary').'"></th>';
			$html .= '<th>ANNIV</th>';
			$html .= '<th>'.WT_Gedcom_Tag::getLabel('EVEN').'</th>';
			$html .= '</tr></thead><tbody>'."\n";
		}

		$value['name'] = $record->getFullName();
		$value['url'] = $record->getHtmlUrl();
		if ($record->getType()=="INDI") {
			$value['sex'] = $record->getSexImage();
		} else {
			$value['sex'] = '';
		}
		$filtered_events[] = $value;
	}

	foreach ($filtered_events as $n=>$value) {
		$html .= "<tr>";
		//-- Record name(s)
		$name = $value['name'];
		$html .= '<td class="wrap">';
		//PERSO Add Sosa Icon to Families
		if ($value['record']->getType()=="FAM") {
			$exp = explode(' + ', $name);
			$husb = $value['record']->getHusband();
			if($husb){
				$dhusb = new WT_Perso_Person($husb);
				$exp[0] .= ' '.WT_Perso_Functions_Print::formatSosaNumbers($dhusb->getSosaNumbers(), 1, 'smaller');
			}
			$wife = $value['record']->getWife();
			if($wife){
				$dwife = new WT_Perso_Person($wife);
				$exp[1] .= ' '.WT_Perso_Functions_Print::formatSosaNumbers($dwife->getSosaNumbers(), 1, 'smaller');
			}
			$name = implode(' + ', $exp);
		}
		//END PERSO
		$html .= '<a href="'.$value['url'].'">'.$name.'</a>';
		if ($value['record']->getType()=="INDI") {
			$html .= $value['sex'];
			//PERSO Add Sosa Icon
			$dindi = new WT_Perso_Person($value['record']);
			$html .= WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'smaller');
			//END PERSO
		}
		$html .= '</td>';
		//-- NAME
		$html .= '<td>'; //hidden by datatables code
		$html .= $value['record']->getSortName();
		$html .= '</td>';
		//-- Event date
		$html .= '<td class="wrap">';
		$html .= $value['date']->Display(empty($SEARCH_SPIDER));
		$html .= '</td>';
		//-- Event date (sortable)
		$html .= '<td>'; //hidden by datatables code
		$html .= $n;
		$html .= '</td>';
		//-- Anniversary
		$anniv = $value['anniv'];
		$html .= '<td>'.($anniv ? WT_I18N::number($anniv) : '&nbsp;').'</td><td>'.$anniv.'</td>';
		//-- Event name
		$html .= '<td class="wrap">';
		$html .= '<a href="'.$value['url'].'">'.WT_Gedcom_Tag::getLabel($value['fact']).'</a>';
		$html .= '&nbsp;</td>';

		$html .= '</tr>'."\n";
	}

	if ($output!=0) {
		$html .= '</tbody></table>';
	}

	// Print a final summary message about restricted/filtered facts
	$summary = "";
	if ($endjd==WT_CLIENT_JD) {
		// We're dealing with the Today's Events block
		if ($output==0) {
			if ($filter==0) {
				$summary = WT_I18N::translate('No events exist for today.');
			} else {
				$summary = WT_I18N::translate('No events for living people exist for today.');
			}
		}
	} else {
		// We're dealing with the Upcoming Events block
		if ($output==0) {
			if ($filter==0) {
				if ($endjd==$startjd) {
					$summary = WT_I18N::translate('No events exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as "tomorrow"
					$summary = WT_I18N::plural('No events exist for the next %s day.', 'No events exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			} else {
				if ($endjd==$startjd) {
					$summary = WT_I18N::translate('No events for living people exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as "tomorrow"
					$summary = WT_I18N::plural('No events for living people exist for the next %s day.', 'No events for living people exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			}
		}
	}
	if ($summary!="") {
		$html .= '<strong>'. $summary. '</strong>';
	}

	return $html;
}

/**
 * print a list of events
 *
 * This performs the same function as print_events_table(), but formats the output differently.
 */
function print_events_list($startjd, $endjd, $events='BIRT MARR DEAT', $only_living=false, $sort_by='anniv') {
	// Did we have any output?  Did we skip anything?
	$output = 0;
	$filter = 0;
	$filtered_events = array();
	$html = '';
	foreach (get_events_list($startjd, $endjd, $events) as $value) {
		$record = WT_GedcomRecord::getInstance($value['id']);
		//-- only living people ?
		if ($only_living) {
			if ($record->getType()=="INDI" && $record->isDead()) {
				$filter ++;
				continue;
			}
			if ($record->getType()=="FAM") {
				$husb = $record->getHusband();
				if (is_null($husb) || $husb->isDead()) {
					$filter ++;
					continue;
				}
				$wife = $record->getWife();
				if (is_null($wife) || $wife->isDead()) {
					$filter ++;
					continue;
				}
			}
		}

		// Privacy
		if (!$record->canDisplayDetails() || !canDisplayFact($record->getXref(), $record->getGedId(), $value['factrec'])) {
			continue;
		}
		$output ++;

		$value['name'] = $record->getFullName();
		$value['url'] = $record->getHtmlUrl();
		//PERSO Add Sosa Icon
		if ($record->getType()=="INDI") {
			$dindi = new WT_Perso_Person($record);
			$value['sex'] = $record->getSexImage();
			$value['sosa'] = WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'smaller');
		} else {
			$value['sex'] = '';
			$value['sosa'] = '';
		}
		//END PERSO 
		$filtered_events[] = $value;
	}

	// Now we've filtered the list, we can sort by event, if required
	switch ($sort_by) {
	case 'anniv':
		uasort($filtered_events, 'event_sort');
		break;
	case 'alpha':
		uasort($filtered_events, 'event_sort_name');
		break;
	}

	foreach ($filtered_events as $value) {
		//PERSO Add Sosa Icon
		$html .= "<a href=\"".$value['url']."\" class=\"list_item name2\">".$value['name']."</a>".$value['sex'].$value['sosa'];
		//END PERSO
		$html .= "<br><div class=\"indent\">";
		$html .= WT_Gedcom_Tag::getLabel($value['fact']).' - '.$value['date']->Display(true);
		if ($value['anniv']!=0) $html .= " (" . WT_I18N::translate('%s year anniversary', $value['anniv']).")";
		if (!empty($value['plac'])) $html .= " - <a href=\"".get_place_url($value['plac'])."\">".$value['plac']."</a>";
		$html .= "</div>";
	}

	// Print a final summary message about restricted/filtered facts
	$summary = "";
	if ($endjd==WT_CLIENT_JD) {
		// We're dealing with the Today's Events block
		if ($output==0) {
			if ($filter==0) {
				$summary = WT_I18N::translate('No events exist for today.');
			} else {
				$summary = WT_I18N::translate('No events for living people exist for today.');
			}
		}
	} else {
		// We're dealing with the Upcoming Events block
		if ($output==0) {
			if ($filter==0) {
				if ($endjd==$startjd) {
					$summary = WT_I18N::translate('No events exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as "tomorrow"
					$summary = WT_I18N::plural('No events exist for the next %s day.', 'No events exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			} else {
				if ($endjd==$startjd) {
					$summary = WT_I18N::translate('No events for living people exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as "tomorrow"
					$summary = WT_I18N::plural('No events for living people exist for the next %s day.', 'No events for living people exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			}
		}
	}
	if ($summary) {
		$html .= "<b>". $summary. "</b>";
	}

	return $html;
}

// print a chart by age using Google chart API
function print_chart_by_age($data, $title) {
	$count = 0;
	$agemax = 0;
	$vmax = 0;
	$avg = 0;
	foreach ($data as $age=>$v) {
		$n = strlen($v);
		$vmax = max($vmax, $n);
		$agemax = max($agemax, $age);
		$count += $n;
		$avg += $age*$n;
	}
	if ($count<1) return;
	$avg = round($avg/$count);
	$chart_url = "https://chart.googleapis.com/chart?cht=bvs"; // chart type
	$chart_url .= "&amp;chs=725x150"; // size
	$chart_url .= "&amp;chbh=3,2,2"; // bvg : 4,1,2
	$chart_url .= "&amp;chf=bg,s,FFFFFF99"; //background color
	$chart_url .= "&amp;chco=0000FF,FFA0CB,FF0000"; // bar color
	$chart_url .= "&amp;chdl=".rawurlencode(WT_I18N::translate('Males'))."|".rawurlencode(WT_I18N::translate('Females'))."|".rawurlencode(WT_I18N::translate('Average age').": ".$avg); // legend & average age
	$chart_url .= "&amp;chtt=".rawurlencode($title); // title
	$chart_url .= "&amp;chxt=x,y,r"; // axis labels specification
	$chart_url .= "&amp;chm=V,FF0000,0,".($avg-0.3).",1"; // average age line marker
	$chart_url .= "&amp;chxl=0:|"; // label
	for ($age=0; $age<=$agemax; $age+=5) {
		$chart_url .= $age."|||||"; // x axis
	}
	$chart_url .= "|1:||".rawurlencode(WT_I18N::percentage($vmax/$count)); // y axis
	$chart_url .= "|2:||";
	$step = $vmax;
	for ($d=floor($vmax); $d>0; $d--) {
		if ($vmax<($d*10+1) && fmod($vmax, $d)==0) $step = $d;
	}
	if ($step==floor($vmax)) {
		for ($d=floor($vmax-1); $d>0; $d--) {
			if (($vmax-1)<($d*10+1) && fmod(($vmax-1), $d)==0) $step = $d;
		}
	}
	for ($n=$step; $n<$vmax; $n+=$step) {
		$chart_url .= $n."|";
	}
	$chart_url .= rawurlencode($vmax." / ".$count); // r axis
	$chart_url .= "&amp;chg=100,".round(100*$step/$vmax, 1).",1,5"; // grid
	$chart_url .= "&amp;chd=s:"; // data : simple encoding from A=0 to 9=61
	$CHART_ENCODING61 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	for ($age=0; $age<=$agemax; $age++) {
		$chart_url .= $CHART_ENCODING61[floor(substr_count($data[$age], "M")*61/$vmax)];
	}
	$chart_url .= ",";
	for ($age=0; $age<=$agemax; $age++) {
		$chart_url .= $CHART_ENCODING61[floor(substr_count($data[$age], "F")*61/$vmax)];
	}
	$html = '<img src="'. $chart_url. '" alt="'. $title. '" title="'. $title. '" class="gchart">';
	return $html;
}

// print a chart by decade using Google chart API
function print_chart_by_decade($data, $title) {
	$count = 0;
	$vmax = 0;
	foreach ($data as $age=>$v) {
		$n = strlen($v);
		$vmax = max($vmax, $n);
		$count += $n;
	}
	if ($count<1) return;
	$chart_url = "https://chart.googleapis.com/chart?cht=bvs"; // chart type
	$chart_url .= "&amp;chs=360x150"; // size
	$chart_url .= "&amp;chbh=3,3"; // bvg : 4,1,2
	$chart_url .= "&amp;chf=bg,s,FFFFFF99"; //background color
	$chart_url .= "&amp;chco=0000FF,FFA0CB"; // bar color
	$chart_url .= "&amp;chtt=".rawurlencode($title); // title
	$chart_url .= "&amp;chxt=x,y,r"; // axis labels specification
	$chart_url .= "&amp;chxl=0:|&lt;|||"; // <1570
	for ($y=1600; $y<2030; $y+=50) {
		$chart_url .= $y."|||||"; // x axis
	}
	$chart_url .= "|1:||".rawurlencode(WT_I18N::percentage($vmax/$count)); // y axis
	$chart_url .= "|2:||";
	$step = $vmax;
	for ($d=floor($vmax); $d>0; $d--) {
		if ($vmax<($d*10+1) && fmod($vmax, $d)==0) $step = $d;
	}
	if ($step==floor($vmax)) {
		for ($d=floor($vmax-1); $d>0; $d--) {
			if (($vmax-1)<($d*10+1) && fmod(($vmax-1), $d)==0) $step = $d;
		}
	}
	for ($n=$step; $n<$vmax; $n+=$step) {
		$chart_url .= $n."|";
	}
	$chart_url .= rawurlencode($vmax." / ".$count); // r axis
	$chart_url .= "&amp;chg=100,".round(100*$step/$vmax, 1).",1,5"; // grid
	$chart_url .= "&amp;chd=s:"; // data : simple encoding from A=0 to 9=61
	$CHART_ENCODING61 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	for ($y=1570; $y<2030; $y+=10) {
		$chart_url .= $CHART_ENCODING61[floor(substr_count($data[$y], "M")*61/$vmax)];
	}
	$chart_url .= ",";
	for ($y=1570; $y<2030; $y+=10) {
		$chart_url .= $CHART_ENCODING61[floor(substr_count($data[$y], "F")*61/$vmax)];
	}
	$html = '<img src="'. $chart_url. '" alt="'. $title. '" title="'. $title. '" class="gchart">';
	return $html;
}
