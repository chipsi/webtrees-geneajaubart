<?php
// Header for webtrees theme
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009 PGV Development Team.  All rights reserved.
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
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

global $WT_IMAGES;
// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript(WT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(WT_JQUERY_WHEELZOOM_URL)
	->addInlineJavascript('activate_colorbox();')
	->addInlineJavascript('jQuery.extend(jQuery.colorbox.settings, { width:"85%", height:"85%", transition:"none", slideshowStart:"'. WT_I18N::translate('Play').'", slideshowStop:"'. WT_I18N::translate('Stop').'", title: function() { var img_title = jQuery(this).data("title"); return img_title; } } );');
?>
<!DOCTYPE html>
<html <?php echo WT_I18N::html_markup(); ?>>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php echo header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL); ?>
	<title><?php echo WT_Filter::escapeHtml($title); ?></title>
	<link rel="icon" href="<?php echo WT_CSS_URL; ?>favicon.png" type="image/png">
	<link rel="stylesheet" type="text/css" href="<?php echo WT_THEME_URL; ?>jquery-ui-1.10.3/jquery-ui-1.10.3.custom.css">
	<link rel="stylesheet" type="text/css" href="<?php echo WT_CSS_URL; ?>style.css">
	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="<?php echo WT_CSS_URL; ?>msie.css">
	<![endif]-->
	<?php //PERSO Add extra style sheet for personal additions  ?>
	<link rel="stylesheet" type="text/css" href="<?php echo WT_CSS_URL; ?>style.extra.css">
	<?php 
	// and Java script for Certificate Module
	if(WT_Perso_Certificate::isModuleOperational())	$this->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.'perso_certificates/js/activatecolorbox.js');
	//END PERSO
	?>
	<?php if (WT_USE_LIGHTBOX) { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo WT_STATIC_URL, WT_MODULES_DIR; ?>lightbox/css/album_page.css">
	<?php } ?>
</head>
<body id="body">
	<?php if ($view!='simple') { ?>
	<div id="header">
		<div class="header_img">
			<img src="<?php echo WT_CSS_URL; ?>images/webtrees.png" width="242" height="50" alt="<?php echo WT_WEBTREES; ?>">
		</div>
		<ul id="extra-menu" class="makeMenu">
			<li>
				<?php
				if (WT_USER_ID) {
					echo '<a href="edituser.php">', WT_I18N::translate('Logged in as '), ' ', getUserFullName(WT_USER_ID), '</a></li> <li>', logout_link();
				} else {
					echo login_link();
				}
	//PERSO Extend header
	$hook_print_header = new WT_Perso_Hook('h_print_header');
	$hook_print_header->execute();
	echo '</li>';
	//END PERSO
				?>
			</li>
			<?php echo WT_MenuBar::getFavoritesMenu(); ?>
			<?php echo WT_MenuBar::getThemeMenu(); ?>
			<?php echo WT_MenuBar::getLanguageMenu(); ?>
		</ul>
		<div class="title" dir="auto">
			<?php echo  WT_TREE_TITLE; ?>
		</div>
		<div class="header_search">
			<form action="search.php" method="post">
				<input type="hidden" name="action" value="general">
				<input type="hidden" name="ged" value="<?php echo WT_GEDCOM; ?>">
				<input type="hidden" name="topsearch" value="yes">
				<input type="search" name="query" size="25" placeholder="<?php echo WT_I18N::translate('Search'); ?>" dir="auto">
				<input type="image" class="image" src="<?php echo $WT_IMAGES['search']; ?>" alt="<?php echo WT_I18N::translate('Search'); ?>" title="<?php echo WT_I18N::translate('Search'); ?>">
			</form>
		</div>
		<div id="topMenu">
			<ul id="main-menu">
				<?php echo WT_MenuBar::getGedcomMenu();   ?>
				<?php echo WT_MenuBar::getMyPageMenu();   ?>
				<?php echo WT_MenuBar::getChartsMenu();   ?>
				<?php echo WT_MenuBar::getListsMenu();    ?>
				<?php echo WT_MenuBar::getCalendarMenu(); ?>
				<?php echo WT_MenuBar::getReportsMenu();  ?>
				<?php echo WT_MenuBar::getSearchMenu();   ?>
				<?php echo implode('', WT_MenuBar::getModuleMenus()); ?>
			</ul>
		</div>
	</div>
	<?php } ?>
	<?php echo $javascript; ?>
	<?php echo WT_FlashMessages::getHtmlMessages(); ?>
	<div id="content">
