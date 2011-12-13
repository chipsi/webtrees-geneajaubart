<?php
/**
 * Displays the patronymic lineage page.
 *
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.'includes/functions/functions_print_lists.php';

global $SEARCH_SPIDER, $SURNAME_LIST_STYLE, $WT_IMAGES, $UNKNOWN_NN, $controller;

$controller=new WT_Controller_Base();

define('WT_ICON_RINGS', '<img src="'.$WT_IMAGES['rings'].'" alt="'.WT_Gedcom_Tag::getLabel('MARR').'" title="'.WT_Gedcom_Tag::getLabel('MARR').'" />');

// We show three different lists: initials, surnames and individuals
// Note that the data may contain special chars, such as surname="<unknown>",
$alpha   =safe_GET('alpha', WT_REGEX_UNSAFE); // All surnames beginning with this letter where "@"=unknown and ","=none
$surname =safe_GET('surname', WT_REGEX_UNSAFE); // All indis with this surname.  NB - allow ' and "
$show_all=safe_GET('show_all', array('no','yes'), 'no'); // All indis

// Make sure selections are consistent.
// i.e. can't specify show_all and surname at the same time.
if ($show_all=='yes') {
	$alpha='';
	$surname='';
	$legend=WT_I18N::translate('All');
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&show_all=yes'.'&amp;ged='.WT_GEDURL;
	$show=safe_GET('show', array('surn', 'lineage'), 'surn');
} elseif ($surname) {
	$alpha=WT_Query_Name::initialLetter($surname);
	$show_all='no';
	if ($surname=='@N.N.') {
		$legend=$UNKNOWN_NN;
	} else {
		$legend=htmlspecialchars($surname);
	}
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&surname='.rawurlencode($surname).'&amp;ged='.WT_GEDURL;
	$show='lineage'; // SURN list makes no sense here
}  elseif ($alpha=='@') {
	$show_all='no';
	$legend=$UNKNOWN_NN;
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($alpha).'&amp;ged='.WT_GEDURL;
	$show='lineage'; // SURN list makes no sense here
	$surname='@N.N.';
} elseif ($alpha==',') {
	$show_all='no';
	$legend=WT_I18N::translate('None');
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($alpha).'&amp;ged='.WT_GEDURL;
	$show='none'; // Don't show lists until something is chosen
} elseif ($alpha) {
	$show_all='no';
	$legend=htmlspecialchars($alpha).'…';
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($alpha).'&amp;ged='.WT_GEDURL;
	$show=safe_GET('show', array('surn', 'lineage'), 'surn');
} else {
	$show_all='no';
	$legend='…';
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&amp;ged='.WT_GEDURL;
	$show='none'; // Don't show lists until something is chosen
}

$controller
	->setPageTitle(WT_I18N::translate('Patronymic Lineages').' : '.$legend)
	->pageHeader();

echo '<h2 class="center">', WT_I18N::translate('Patronymic Lineages'), '</h2>';

// Print a selection list of initial letters
$list=array();
foreach (WT_Query_Name::surnameAlpha(false, false, WT_GED_ID) as $letter=>$count) {
	switch ($letter) {
	case '@':
		$html=$UNKNOWN_NN;
		break;
	case ',':
		break;
	default:
		$html=htmlspecialchars($letter);
		break;
	}
	if ($letter!='' && $count) {
		if ($letter==$alpha) {
			$list[]='<a href="module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($letter).'&amp;ged='.WT_GEDURL.'" class="warning" title="'.$count.'">'.$html.'</a>';
		} else {
			$list[]='<a href="module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($letter).'&amp;ged='.WT_GEDURL.'" title="'.$count.'">'.$html.'</a>';
		}
	} else {
		$list[]=$html;
	}
}

// Search spiders don't get the "show all" option as the other links give them everything.
if (!$SEARCH_SPIDER) {
	if ($show_all=='yes') {
		$list[]='<span class="warning">'.WT_I18N::translate('All').'</span>';
	} else {
		$list[]='<a href="module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&show_all=yes'.'&amp;ged='.WT_GEDURL.'">'.WT_I18N::translate('All').'</a>';
	}
}

echo '<p class="center alpha_index">', join(' | ', $list), '</p>';

if ($show=='lineage' || $show=='surn') {
	$surns=WT_Query_Name::surnames($surname, $alpha, false, false, WT_GED_ID);
	if ($show=='surn') {
		// Show the surname list
		switch ($SURNAME_LIST_STYLE) {
		case 'style1';
			echo format_surname_list($surns, 3, true, 'module.php', '&mod=perso_patronymiclineage&mod_action=patronymiclineage');
			break;
		case 'style3':
			echo format_surname_tagcloud($surns, 'module.php', true, '&mod=perso_patronymiclineage&mod_action=patronymiclineage');
			break;
		case 'style2':
		default:
			echo format_surname_table($surns, 'module.php', '&mod=perso_patronymiclineage&mod_action=patronymiclineage');
			break;
		}
	} else {
		//Link to indilist
		echo '<p class="center"><strong><a href="indilist.php?ged='.WT_GEDCOM.'&surname='.urlencode($surname).'">'.WT_I18N::translate('Go to the list of individuals with surname %s', check_NN($legend)).'</a></strong></p>';
		
		if ($legend && $show_all=='no') {
			$legend=WT_I18N::translate('Individuals in %s lineages', check_NN($legend));
		}
		
		WT_Perso_Functions_PatronymicLineage::printLineages($surname, $legend, WT_GED_ID);
	}
}

?>