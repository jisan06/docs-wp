<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

function easydoc_uninstall()
{
    try { 
        \Foliokit::getObject('database.driver.mysqli')->execute(\EasyDocLabs\EasyDoc\InstallHelper::getFilesystem()->get_contents(__DIR__.'/uninstall.sql'), \EasyDocLabs\Library\Database::MULTI_QUERY);
    } catch (\Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw $e;
        }
    }

    delete_option('easydoc_installed');
}