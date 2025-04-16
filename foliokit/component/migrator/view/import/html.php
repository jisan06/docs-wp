<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

/**
 * Import View Class.
 */
class ViewImportHtml extends Base\ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'decorator'  => 'foliokit'
        ));

        parent::_initialize($config);
    }

    /**
     * The extension name.
     *
     * @var string
     */
    protected $_extension;

    public function isCollection()
    {
        return true;
    }

    public function getRoute($route = '', $fqr = false, $escape = true)
    {
        if (is_string($route)) {
            parse_str(trim($route), $parts);
        } else {
            $parts = $route;
        }

        if (!isset($parts['option'])) {
            $parts['option'] = $this->getObject('request')->getQuery()->option;
        }

        return parent::getRoute($parts, $fqr, $escape);
    }

    /**
     * Finds the maximum possible upload size based on a few different INI settings
     *
     * @return int
     */
    public static function getServerUploadLimit()
    {
        $convertToBytes = function($value) {
            $keys = array('k', 'm', 'g');
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

    /**
     * Missing dependencies getter.
     *
     * @return array An array containing missing dependencies.
     */
    public function getMissingDependencies()
    {
        $requirements = array(
            'zip' => array(
                class_exists('ZipArchive'),
                'ZipArchive class is needed for the export process.'
            ),
            'tmp' => array(
                is_writable(\EasyDocLabs\WP::get_temp_dir()),
                'Please make sure tmp directory in your site root is writable'
            )
        );

        $return = array();

        foreach ($requirements as $key => $value) {
            if ($value[0] === false) {
                $return[$key] = $value[1];
            }
        }

        return $return;
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        $data = $this->getData();

        $context->data->go_back              = $this->getObject('request')->getReferrer();
        $context->data->missing_dependencies = $this->getMissingDependencies();
        $context->data->server_upload_limit  = $this->getServerUploadLimit();
        $context->data->extension            = isset($data['extension']) ? $data['extension'] : null;

        parent::_fetchData($context);
    }
}
