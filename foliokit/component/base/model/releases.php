<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Model for the Component's Releases.
 *
 * @author  Israel Canasa <https://github.com/raeldc>
 * @package EasyDocLabs\Component\Base
 */
class ModelReleases extends ModelResources
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('version', 'ascii', '*')
            ->insert('plugin', 'url', null, true, true);
    }

    protected function _actionFetch(Library\ModelContext $context)
    {
        $context->append([
            'options' => [
                'identity_key' => 'version'
            ]
        ]);

        if($this->getState()->version != 'current')
        {
            $data = parent::_actionFetch($context);

            if($this->getState()->version == '*') {
                return $data;
            }
            elseif($this->getState()->version != 'latest') {
                $data = $data->find($this->getState()->version);
            }

            return $data->setProperties(\EasyDocLabs\WP::get_plugin_data($this->getState()->plugin), false);
        }
        else $context->data = [\EasyDocLabs\WP::get_plugin_data($this->getState()->plugin)];

        return parent::_actionFetch($context);
    }
}
