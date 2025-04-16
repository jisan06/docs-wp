<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Container Entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelEntityContainer extends Library\ModelEntityRow
{
    public function save()
    {
        $result = parent::save();

        if (!is_dir($this->fullpath)) {
            mkdir($this->fullpath, 0755, true);
        }

        return $result;
    }

    public function disablePublicAccess()
    {
        if (!is_file($this->fullpath.'/.htaccess')) {
            $buffer ='DENY FROM ALL';
            file_put_contents($this->fullpath.'/.htaccess', $buffer);
        }

        if (!is_file($this->fullpath.'/web.config')) {
            $buffer ='<?xml version="1.0" encoding="utf-8" ?>
<system.webServer>
    <security>
        <authorization>
            <remove users="*" roles="" verbs="" />
            <add accessType="Allow" roles="Administrators" />
        </authorization>
    </security>
</system.webServer>';
            file_put_contents($this->fullpath.'/web.config', $buffer);
        }
    }

    public function getPropertyRelativePath()
    {
        $path = $this->fullpath;
        $root = str_replace('\\', '/', \Foliokit::getInstance()->getRootPath());

        return str_replace($root.'/', '', $path);
    }

    public function getPropertyFullpath()
    {
        $result = $this->getProperty('path');

        // Prepend with site root if it is a relative path
        if (!preg_match('#^(?:[a-z]\:|~*/)#i', $result)) {
            $result = rtrim(\Foliokit::getInstance()->getRootPath(), '/').'/'.$result;
        }

        $result = rtrim(str_replace('\\', '/', $result), '\\');

        return $result;
    }

	public function toArray()
	{
		$data = parent::toArray();
        $data['relative_path'] = $this->getProperty('relative_path');
		$data['parameters']    = $this->getParameters()->toArray();
        $data['server_upload_limit'] = static::getServerUploadLimit();

		return $data;
	}

    /**
     * Finds the maximum possible upload size based on a few different INI settings
     *
     * @return int
     */
    public static function getServerUploadLimit()
    {
        $convertToBytes = function($value) {
            $keys = ['k', 'm', 'g'];
            $last_char = strtolower(substr($value, -1));
            $value = (int) $value;

            if (in_array($last_char, $keys)) {
                $value *= pow(1024, array_search($last_char, $keys)+1);
            }

            return $value;
        };

        $max_upload = $convertToBytes(ini_get('upload_max_filesize'));
        $max_post   = $convertToBytes(ini_get('post_max_size'));

        return min($max_post, $max_upload);
    }
}
