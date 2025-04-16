<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ConnectHandlerActivities extends ConnectHandlerAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['property' => 'path', 'tasks' => ['activities-status']]);

        parent::_initialize($config);
    }

    public function handle(Library\ControllerContextInterface $context)
    {
        $request  = $context->getRequest();
        $status   = Library\HttpResponse::OK;
        $response = [];

        if ($request->getMethod() != 'GET')
        {
            $enabled = (int) $request->getData()->enabled;

            $this->params->set('activities', $enabled);

            if ($this->_saveParameters())
            {
                $response = [
                    'enabled' => (bool) $enabled
                ];
            }
            else $status = Library\HttpResponse::INTERNAL_SERVER_ERROR;
        }
        else $response = ['enabled' => (bool) $this->params->get('activities')];

        $context->getResponse()->setStatus($status)->setContent(\EasyDocLabs\WP::wp_json_encode($response), 'application/json');
    }
    
    protected function _saveParameters()
    {
        if (!$this->getConfig()->id) {
            throw new \RuntimeException('Cannot find Plugin ID');
        }

        $query = $this->getObject('database.query.update')
                      ->table('extensions')
                      ->values('params = :params')
                      ->where('extension_id = :extension_id')
                      ->bind([
                          'params' => $this->params->toString(), 'extension_id' => $this->getConfig()->id
                      ]);

        return $this->getObject('database.adapter.mysqli')->execute($query, Library\Database::RESULT_USE);
    }
}