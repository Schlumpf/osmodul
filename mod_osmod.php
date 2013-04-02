<?php
/*------------------------------------------------------------------------
# mod_osmod
# ------------------------------------------------------------------------
# author    Martin Kröll
# copyright Copyright (C) 2012-2013 Martin Kröll. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(dirname(__FILE__).'/helper.php'); 

// include skripts/styles to the header
$document =& JFactory::getDocument();
$document->addStyleSheet('modules/mod_osmod/leaflet/leaflet.css');
$document->addScript('modules/mod_osmod/leaflet/leaflet.js');
$document->addStyleDeclaration( ModOsmodHelper::style($params, $module->id) );

// create javascript
$js = ModOsmodHelper::javascript($params, $module->id);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

// Modell einbinden
require(JModuleHelper::getLayoutPath('mod_osmod'));
?>