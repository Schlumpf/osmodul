<?php
/*------------------------------------------------------------------------
# mod_osmod
# ------------------------------------------------------------------------
# author    Martin Kröll
# copyright Copyright (C) 2012-2015 Martin Kröll. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
--------------------------------------------------------------------------
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class ModOsmodHelper{
    private static function wxh($string){
        $exp =  explode('x', $string);
        return $exp[0].','.$exp[1];
    }

    private static function imagery($bl, $style){
        $return = "";
        if($bl == 'mapnikde')           $return = '<a href="http://www.openstreetmap.de/">Openstreetmap.de</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';
        else if($bl == 'stamenwater')   $return = '<a href="http://stamen.com">Stamen Design</a>, <a href="https://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>';
        else if($bl == 'opentopomap')   $return = '<a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>), <a href="http://viewfinderpanoramas.org">SRTM</a>';
        else if($bl == 'openmapsurfer') $return = '<a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a>';
        else if($bl == 'humanitarian')  $return = '<a href="https://hotosm.org/">Humanitarian OpenStreetMap Team</a>';
        else if($bl == 'custom')        $return = $style;
        return $return;
    }

    private static function scale($params, $id){
        $return = "";
        if (count($params->get('scale')) > 0) {
           $return .= "L.control.scale({";

            if (in_array('metric', $params->get('scale'))) {
                $return .= "metric:true";
            } else {
                $return .= "metric:false";
            }

            if (in_array('imperial', $params->get('scale'))) {
                $return .= ",imperial:true";
            } else {
                $return .= ",imperial:false";
            }

            $return .= "}).addTo(map".$id.");\n";
        }
        return $return;
    }

    // to header
    public static function style($params, $id){
        $style = '#map'.$id.'{'
                .'    height:'.$params->get('height', 200).'px;'
                .'}'
                .'.leaflet-control-attribution a{' // reset link style
                .'    color: inherit !important;'
                .'    font-weight: normal !important;'
                .'}';
        return $style;
    }

    // Multi-marker: Code for custom markers
    private static function mpCustompin($parCustom, $id){
        $ret = "";
        $customs = array();

        // if no custom pins, return
        if($parCustom == '') return '';

        // Einzelne Einträge trennen
        $exp = explode(';', $parCustom);

        // Custompins parsen
        foreach($exp as $c){
            if($c != ''){
                preg_match('/#(?P<name>\w+)\s*\{\"(?P<iconUrl>(?:\/?\w+)+\.\w+)\"\s*\,\s*\((?P<iconSize>\d+x\d+)\)\s*\,\s*(?:(?:\"(?P<shadowUrl>(?:(?:\/?\w+)+\.\w+))\")|(?:)|(?:\"\"))\s*\,\s*\((?P<shadowSize>\d+x\d+)\)\s*\,\s*\((?P<iconAnchor>\-?\d+x\-?\d+)\)\s*\,\s*\((?P<popupAnchor>\-?\d+x\-?\d+)\)\s*\}/',$c,$treffer);

                $ret .= "var mpC".$id."_".$treffer['name']." = L.Icon.extend({
                                                                 options: {
                                                                   iconUrl: \"".JURI::base().$treffer['iconUrl']."\",
                                                                   shadowUrl: \"".JURI::base().$treffer['shadowUrl']."\",
                                                                   iconSize: new L.Point(".self::wxh($treffer['iconSize'])."),
                                                                   shadowSize: new L.Point(".self::wxh($treffer['shadowSize'])."),
                                                                   iconAnchor: new L.Point(".self::wxh($treffer['iconAnchor'])."),
                                                                   popupAnchor: new L.Point(".self::wxh($treffer['popupAnchor']).")
                                                                 }
                                                               });\n";
            }
        }
        return $ret;
    }

    // Multi-marker: Code for custom popups
    private static function mpPopups($parPopups, $id){
        $ret = "";
        $popups = array();

        // Wenn keine Popups gegben sind, abbrechen
        if($parPopups == '') return '';

        // Einzelne Einträge trennen
        $exp = explode('};', $parPopups);

        // Popups parsen
        foreach($exp as $p){
            if($p != ''){
                preg_match('/#(?P<name>\w+)\s*\{\s*(?P<text>.*)\s*\}/', $p.'}', $treffer);

                $text = str_replace("'", "\\'", str_replace( array("\r\n", "\n", "\r") , "" , $treffer['text']));
                $ret .= "var mpP".$id."_".$treffer['name']." = '".$text."';\n";
            }
        }
        // Code zurückgeben
        return $ret;
    }

    //Multi-marker: Code for multiple markers
    private static function mpPins($parPins, $id){
        $ret = "";
        $markers = array();

        // Wenn keine Pins gegben sind, abbrechen
        if($parPins == '') return '';

        // Einzelne Einträge trennen
        $exp = explode(';', $parPins);

        // Pins parsen
        foreach($exp as $pin){
            if($pin != ''){
                preg_match('/#(?P<name>\w+)\s*\{\s*\(\s*(?P<coords>\-?\d+\.?\d*\s*\,\s*\-?\d+\.?\d*\s*)\)\s*\,\s*(?:#(?P<skin>\w+))?\s*\,\s*(?:\{\s*#(?P<popup>\w+)\s*\,\s*(?P<show>click|always|immediately)\s*\})?\s*\}/', $pin, $treffer);

                $ret .= "var mpK".$id."_".$treffer['name']."  = new L.LatLng(".$treffer['coords'].");\n";                // Koordinaten anlegen
                $cp   = ''; if($treffer['skin'] != '') $cp = ", {icon: new mpC".$id."_".$treffer['skin']."()}";          // Custom Icon verknüpfen
                $ret .= "var mpM".$id."_".$treffer['name']." = new L.Marker(mpK".$id."_".$treffer['name'].$cp.");\n";    // Marker anlegen
                $ret .= "map".$id.".addLayer(mpM".$id."_".$treffer['name'].");\n";                                       // Marker auf Karte setzen

                // Popup verknüpfen
                if($treffer['popup'] != ''){
                    $ret .= "mpM".$id."_".$treffer['name'].".bindPopup(mpP".$id."_".$treffer['popup'].");\n";
                    if($treffer['show'] == 'always' | $treffer['show'] == 'immediately') {
                        $ret .= "mpM".$id."_".$treffer['name'].".openPopup();\n";
                    }
                }
            }
        }
        // Code zurück geben
        return $ret;
    }

    // Create Javascript code (main)
    public static function javascript($params, $id){
        // load baselayerURL
        $baselayerSettings = '';
        if     ($params->get('baselayer', 'mapnik') == 'mapnikde')      { $baselayerURL = 'http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png';           $baselayerSettings = "maxZoom: 18, "; }
        else if($params->get('baselayer', 'mapnik') == 'stamenwater')   { $baselayerURL = 'https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.png';   $baselayerSettings = "subdomains: 'abcd', minZoom: 1, maxZoom: 16, "; }
        else if($params->get('baselayer', 'mapnik') == 'opentopomap')   { $baselayerURL = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';                       $baselayerSettings = "maxZoom: 16, "; }
        else if($params->get('baselayer', 'mapnik') == 'openmapsurfer') { $baselayerURL = 'http://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}';     $baselayerSettings = "maxZoom: 20, "; }
        else if($params->get('baselayer', 'mapnik') == 'humanitarian')  { $baselayerURL = 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';                  $baselayerSettings = "maxZoom: 20, "; }
        else if($params->get('baselayer', 'mapnik') == 'custom')        { $baselayerURL = $params->get('customBaselayerURL', ''); }
        else   /* unknown layer and mapnik */                           { $baselayerURL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';                     $baselayerSettings = "maxZoom: 19, "; }

        // load start coordinates
        $lat  = $params->get('lat', 50.560715);
        $lon  = $params->get('lon', 7.316633);
        $zoom = $params->get('zoom', 12);

        // create Popup
        $popup = '';
        if( $params->get('popup', 0) > 0 ){
            $text  = str_replace( array("\r\n", "\n", "\r") , "" , $params->get('popuptext', '') );
            $popup = 'marker'.$id.'.bindPopup(\''.$text.'\')';

            if( $params->get('popup', 0) == 2 ) $popup .= '.openPopup()'; // open popup, if user choosed "always"
        }

        // define custom pin
        if($params->get('pin', 1) == 2){
            $pin1path       = JURI::base().$params->get('customPinPath', '');
            $pin1shadowPath = JURI::base().$params->get('customPinShadowPath', '');
            $customIcon = "var customIcon".$id." = L.Icon.extend({
                                                     options: {
                                                       iconUrl: '".$pin1path."',shadowUrl: '".$pin1shadowPath."',
                                                       iconSize: new L.Point(".self::wxh(    $params->get('customPinSize','24x24')     )."),
                                                       shadowSize: new L.Point(".self::wxh(  $params->get('customPinShadowSize','0x0') )."),
                                                       iconAnchor: new L.Point(".self::wxh(  $params->get('customPinOffset','0x0')     )."),
                                                       popupAnchor: new L.Point(".self::wxh( $params->get('customPinPopupOffset','0x0')).")
                                                     }
                                                   })";
        }

        // generate Javascript
        // ----------------------------------------

        // no worldWarp (no wolrd copies, restrict the view to one world)
        if($params->get('noWorldWarp', 0) == 1){
            $nowarp = "noWrap: true, ";
            $worldcopyjump = "worldCopyJump: false, maxBounds: [ [82, -180], [-82, 180] ]";
        }else{
            $nowarp = "noWrap: false, ";
            $worldcopyjump = "worldCopyJump: true";
        }

        // create the map
        $js  = "var map".$id."       = new L.Map('map".$id."', {".$worldcopyjump."});\n";
        $js .= "    map".$id.".attributionControl.setPrefix('');\n";
        $js .= "var baselayer".$id." = new L.TileLayer('".$baselayerURL."', {".$baselayerSettings.$nowarp."attribution: '<a href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\">© OpenStreetMap contributors</a>'});\n";
        $js .= "var koord".$id."     = new L.LatLng(".$lat.", ".$lon.");\n";

        // Scale
        $js .= self::scale($params, $id);

        // Attribution
        if ($params->get('attrLeaflet', 1) == 1) {
            $js .= "map".$id.".attributionControl.addAttribution('Powered by Leaflet');\n";
        }
        if ($params->get('attrImagery', 1) == 1 && self::imagery($params->get('baselayer', 'mapnik'), $params->get('customBaselayer', "")) != '') {
            $js .= "map".$id.".attributionControl.addAttribution('".JText::_('MOD_OSMOD_IMAGERY')." ".self::imagery($params->get('baselayer', 'mapnik'), $params->get('customBaselayer', ""))."');\n";
        }
        if ($params->get('attrModule',  1) == 1) {
            $js .= "map".$id.".attributionControl.addAttribution('".JText::_('MOD_OSMOD_MODULE_BY')." <a href=\"https://extensions.joomla.org/extensions/owner/schlumpf\" target=\"_blank\">Martin Kröll</a>');\n";
        }

        // Pin
        if($params->get('pin', 1) == 2){
            $js .= $customIcon.";\n";
            $js .= "var marker".$id." = new L.Marker(koord".$id.", {icon: new customIcon".$id."()});\n";
        }else{
            $js .= "var marker".$id." = new L.Marker(koord".$id.");\n";
        }
        if($params->get('pin', 1) > 0) $js .= "map".$id.".addLayer(marker".$id.");\n";

        // Karte ausrichten
        $js .= "// set map view\n";
        $js .= "map".$id.".setView(koord".$id.", ".$zoom.").addLayer(baselayer".$id.");\n";

        // Multi-marker
        $js .= "// additional Pins\n";
        $js .= self::mpCustompin( $params->get('custompins', ''), $id ); // Create custom pin styles
        $js .= self::mpPopups(    $params->get('popups', ''),     $id ); // Create Popup contents
        $js .= self::mpPins(      $params->get('pins', ''),       $id ); // Create pins and add Popups

        // Popup anzeigen
        $js .= $popup.";\n";

        // Return Code
        return $js;
    }
}
?>
