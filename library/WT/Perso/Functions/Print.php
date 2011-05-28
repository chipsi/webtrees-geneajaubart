<?php
/**
 * Additional functions for displaying information
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Functions_Print {
	
	/**
	 * Return HTML code to include a flag icon in facts description
	 *
	 * @param string $factrec GEDCOM fact record
	 * @return string HTML code of the inserted flag
	 */
	public static function getFactPlaceIcon($factrec) {
		$html='';
		$ctpl = preg_match("/2 PLAC (.*)/", $factrec, $match);
		if($ctpl>0){
			$iconPlace=WT_Perso_Functions_Map::getPlaceIcon($match[1], 50);
			if(count($iconPlace) != 0){
				$html.='<div class="fact_flag">'.$iconPlace.'</div>';
			}
		}
		return $html;
	}
	
	/**
	 * Returns HTML code to include a place cloud
	 * 
	 * @param array $places Array of places to display in the cloud
	 * @param bool $totals Display totals for a place
	 * @return string Place Cloud HTML Code
	 */
	public static function getPlacesCloud($places, $totals) {
		
		require_once WT_ROOT.'includes/functions/functions_places.php';
		
		$cloud=new Zend_Tag_Cloud(
			array(
				'tagDecorator'=>array(
					'decorator'=>'HtmlTag',
					'options'=>array(
						'htmlTags'=>array(),
						'fontSizeUnit'=>'%',
						'minFontSize'=>100,
						'maxFontSize'=>180
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
		foreach ($places as $place=>$count) {
			$shortplace = self::formatPlaceShort($place, '%1 (%2)');
			$cloud->appendTag(array(
				'title'=>$totals ? WT_I18N::translate('%1$s (%2$d)', $shortplace, $count) : $shortplace,
				'weight'=>$count,
				'params'=>array(
					'url'=> get_place_url($place)
				)
			));
		}
		return (string)$cloud;
	}
	
	/**
	 * Return HTML Code to display individual in non structured list (e.g. Patronymic Lineages)
	 *
	 * @param WT_Person $individual Individual to print
	 * @param bool $isStrong Bolden the name ?
	 * @return string HTML Code for individual item
	 */
	public static function getIndividualForList(WT_Person $individual, $isStrong = true){		
		$html = '';
		$tag = 'em';
		if($isStrong) $tag = 'strong';
		if($individual && $individual->canDisplayDetails()){
			$dindi = new WT_Perso_Person($individual);
			$html = $individual->getSexImage();
			$html .= '<a class="list_item" href="'.
				$individual->getHtmlUrl().
				'" title="'.
				WT_I18N::translate('Informations for individual %s', $individual->getXref()).
				'">';
			$html .= '<'.$tag.'>'.$individual->getFullName().'</'.$tag.'>&nbsp;('.$individual->getXref().')&nbsp;';
			$html .= WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'small');
			$html .= '&nbsp;<span><small><em>'.$dindi->format_first_major_fact(WT_EVENTS_BIRT, 10).'</em></small></span>';
			$html .= '&nbsp;<span><small><em>'.$dindi->format_first_major_fact(WT_EVENTS_DEAT, 10).'</em></small></span>';
			$html .= '</a>';
		}
		else {
			$html .= '<span class=\"list_item\"><'.$tag.'>'.WT_I18N::translate('Private').'</'.$tag.'></span>';
		}
		return $html;
	}
	
	/**
	 * Format date to display short (just years)
	 *
	 * @param Event $eventObj Event to display date
	 * @param boolean $anchor option to print a link to calendar
	 * @return string HTML code for short date
	 */
	public static function formatFactDateShort(&$eventObj, $anchor=false) {	
		global $SEARCH_SPIDER;
		
		$html='';	
		if (!is_object($eventObj)) trigger_error("Must use Event object", E_USER_WARNING);
		$factrec = $eventObj->getGedcomRecord();
		if (preg_match('/2 DATE (.+)/', $factrec, $match)) {
			$date = new WT_Date($match[1]);
			$html.=' '.$date->Display($anchor && !$SEARCH_SPIDER, '%Y');
		} else {
			// 1 DEAT Y with no DATE => print YES
			// 1 DEAT N is not allowed
			// It is not proper GEDCOM form to use a N(o) value with an event tag to infer that it did not happen.
			$factrec = str_replace("\nWT_OLD\n", '', $factrec);
			$factrec = str_replace("\nWT_NEW\n", '', $factrec);
			$factdetail = explode(' ', trim($factrec));
			if (isset($factdetail)) if (count($factdetail) == 3) if (strtoupper($factdetail[2]) == 'Y') {
				$html.=WT_I18N::translate('Yes');
			}		
		}
		return $html;
	}
	
/**
	 * Format place to display short.
	 * The format string should used %n with n to describe the level of division to be printed (in the order of the GEDCOM place).
	 * For instance "%1 (%2)" will display "Subdivision (Town)".
	 *
	 * @param Event $eventObj Event to display date
	 * @param string $format Format of the place
	 * @param boolean $anchor option to print a link to placelist
	 * @return string HTML code for short place
	 */
	public static function formatPlaceShort($place, $format, $anchor=false){
		global $SEARCH_SPIDER;

		$html='';
		$levels = explode(', ', $place);
		$nbLevels = count($levels);
		$displayPlace = $format;
		preg_match_all('/%[^%]/', $displayPlace, $matches);
		foreach ($matches[0] as $match2) {
			$index = str_replace('%', '', $match2);
			if(is_numeric($index) && $index >0 && $index <= $nbLevels){
				$displayPlace = str_replace($match2, $levels[$index-1] , $displayPlace);
			}
			else{
				$displayPlace = str_replace($match2, '' , $displayPlace);
			}
		}			
		if ($anchor && (empty($SEARCH_SPIDER))) {
			// reverse the array so that we get the top level first
			$levels = array_reverse($levels);
			$tempURL = "placelist.php?action=show&amp;";
			foreach ($levels as $pindex=>$ppart) {
				$tempURL .= "parent[{$pindex}]=".rawurlencode($ppart).'&amp;';
			}
			$tempURL .= 'level='.count($levels);
			$html .= '<a href="'.$tempURL.'"> '.PrintReady($displayPlace).'</a>';
		} else {
			$html.=PrintReady($displayPlace);
		}
		return $html;
	}
	
	/**
	 * Format fact place to display short
	 *
	 * @param Event $eventObj Event to display date
	 * @param string $format Format of the place
	 * @param boolean $anchor option to print a link to placelist
	 * @return string HTML code for short place
	 */
	public static function formatFactPlaceShort(&$eventObj, $format, $anchor=false){
		global $SEARCH_SPIDER;
	
		if ($eventObj==null) return '';
		if (!is_object($eventObj)) {
			trigger_error("Object was not sent in, please use Event object", E_USER_WARNING);
			$factrec = $eventObj;
		}
		else $factrec = $eventObj->getGedcomRecord();
	
		$html='';
	
		$ct = preg_match("/2 PLAC (.*)/", $factrec, $match);
		if ($ct>0) {
			$html .= self::formatPlaceShort($match[1], $format);
		}
		return $html;
	}
	
	/**
	 * Format Sosa number to display next to individual details
	 * Possible format are:
	 * 	- 1 (default) : display an image if the individual is a Sosa, independtly of the number of times he is
	 * 	- 2 : display a list of Sosa numbers, with an image, separated by an hyphen.
	 *
	 * @param array $sosatab List of Sosa numbers
	 * @param int $format Format to apply to the Sosa numbers
	 * @param string $size CSS size for the icon. A CSS style css_$size is required
	 * @return string HTML code for the formatted Sosa numbers
	 */
	public static function formatSosaNumbers($sosatab, $format = 1, $size = 'small'){
		global $WT_IMAGES;
		
		$html = '';
		switch($format){
			case 1:
				if($sosatab && count($sosatab)>0){
					$html = '<img src="'.$WT_IMAGES['sosa'].'" title="'.WT_I18N::translate('Sosa').'" alt="'.WT_I18N::translate('Sosa').'" class="sosa_'.$size.'" />';
				}
				break;
			case 2:
				if($sosatab && count($sosatab)>0){
					ksort($sosatab);
					$tmp_html = array();
					foreach ($sosatab as $sosa => $gen) {
						$tmp_html[] = '<img src="'.$WT_IMAGES['sosa'].'" title="'.WT_I18N::translate('Sosa').'" alt="'.WT_I18N::translate('Sosa').'" class="sosa_'.$size.'" />&nbsp;<strong>'.$sosa.'&nbsp;'.WT_I18N::translate('(G%s)', $gen).'</strong>';
					}
					$html = implode(' - ', $tmp_html);
				}
				break;
			default:
				break;
		}
		return $html;
	}
	
} 

?>