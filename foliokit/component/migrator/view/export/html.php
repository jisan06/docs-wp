<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ViewExportHtml extends Base\ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'decorator'  => 'foliokit',
            'auto_fetch' => false
        ));

        parent::_initialize($config);
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

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        parent::_fetchData($context);

        $data  = $this->getData();
        $exporters = Library\ObjectConfig::unbox($data['exporters']);

        $context->data->go_back = $this->getObject('request')->getReferrer();

        if (empty($exporters)) {
            $this->setLayout('error');
            $context->layout = $this->getLayout();
        }
        else
        {
            $labels = array();
            foreach ($exporters as $extension => $exporter)
            {
                $list = $exporter->getIterator();
                $configs = array();
                foreach ($list as $job) {
                    $configs[$job->name] = Library\ObjectConfig::unbox($job);
                }

                $labels[$extension]    = $exporter->getLabel();
                $exporters[$extension] = $configs;
            }

            $context->data->exporters = $exporters;
            $context->data->labels = $labels;
            $context->data->missing_dependencies = $this->getMissingDependencies();
        }
    }

    /**
     * Missing dependencies getter.
     *
     * @return array A list of missing dependencies.
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

        foreach ($requirements as $key => $value)
        {
            if ($value[0] === false) {
                $return[$key] = $value[1];
            }
        }

        return $return;
    }
}
