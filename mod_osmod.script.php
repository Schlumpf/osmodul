<?php
/**
 * @copyright   Copyright (C) Martin Kröll. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

class mod_osmodInstallerScript
{
    // Move to the module site after installation
    function install($parent) {
        // $parent is the class calling this method
        $parent->getParent()->setRedirectURL('index.php?option=com_modules');
    }

    // Show after uninstall message
    function uninstall($parent) {
        echo '<p>' . JText::_('MOD_OSMOD_UNINSTALL') . '</p>';
    }

    // Show after update message
    function update($parent)  {
        // $parent is the class calling this method
        echo '<p>' . JText::sprintf('MOD_OSMOD_UPDATE', $parent->get('manifest')->version) . '</p>';
    }

    // Before install/update/uninstall
    function preflight($type, $parent) {
        // $type is the type of change (install, update or discover_install)
        echo '<p>' . JText::_('MOD_OSMOD_PREFLIGHT') . '<ul>';

        // Remove FR language pack
        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('extension_id', 'name')))
              ->from($db->quoteName('#__extensions'))
              ->where($db->quoteName('element') . ' = '. $db->quote('OSModulFrenchPack'));
        $db->setQuery($query);
        
        $frPack = $db->loadRow();
        if(!empty($frPack)) {
            echo '<li>' . $frPack[1] . '</li>';
            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__extensions'))
                  ->where($db->quoteName('extension_id') . ' = '. $frPack[0]);
            $db->setQuery($query);
            $db->execute();
            rrmdir(JPATH_ROOT . '/modules/mod_osmod/language/fr-FR');
        }
        

        // Remove files from older releases
        $folders = array('/modules/mod_osmod/leaflet',
                         '/modules/mod_osmod/images');

        $files = array('/language/de-DE/de-DE.mod_osmod.ini',
                       '/language/de-DE/de-DE.mod_osmod.sys.ini',
                       '/language/en-GB/en-GB.mod_osmod.ini',
                       '/language/en-GB/en-GB.mod_osmod.sys.ini');
                       
        foreach ($folders as &$f) {
            if (file_exists(JPATH_ROOT . $f)) {
                echo '<li>' . JPATH_ROOT . $f . '</li>';
                rrmdir(JPATH_ROOT . $f);
            }
        }
        
        foreach ($files as &$f) {
            if (file_exists(JPATH_ROOT . $f)) {
                echo '<li>' . JPATH_ROOT . $f . '</li>';
                unlink(JPATH_ROOT . $f);
            }
        }

        echo '</ul>' . JText::_('MOD_OSMOD_DONE') . '</p>';
    }

    // After install/update/uninstall
    function postflight($type, $parent) {}
}
