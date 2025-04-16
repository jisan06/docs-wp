<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\WP;

/**
 * Download controller permissions
 */
class ControllerPermissionDownload extends EasyDoc\ControllerPermissionAbstract
{
    public function canRender()
    {
        $result = false;

        if ($this->_isArchiveRequest() || $this->_canDownload()) {
            $result = true;
        }

        return $result;
    }

    protected function _canDownload()
    {
        return $this->_isPlayerRequest() || $this->_canExecuteList('download_document');
    }

    protected function _isPlayerRequest()
    {
        $result = false;

        $controller = $this->getMixer();
        $request    = $controller->getRequest();
        $query      = $request->getQuery();

        // Check query for player JWT

        if ($query->has('auth_player'))
        {
            $token = $this->getObject('lib:http.token')->fromString($query->auth_player);

            if ($token->verify(WP::wp_salt('AUTH_SALT')))
            {
                // Check subject

                if ($subject = $token->getSubject()) {
                    $result = $controller->getUser()->getEmail() == $subject;
                }

                // Entity check

                $entity = $controller->getModel()->fetch();

                if (!$result && !$entity->isNew() && ($entity->count() === 1) && ($uuid = $token->getClaim('entity'))) {
                    $result = $uuid == $entity->uuid;
                }
            }
        }

        return $result;
    }

    protected function _isArchiveRequest()
    {
        $controller = $this->getMixer();

        // Compress action was already allowed, safe enough to allow download here
        
        return $controller->isCompressible() && $controller->getRequest()->getQuery()->has('archive');
    }



    /**
     * Avoids archive compression for batch downloading if downloads aren't allowed
     */
    public function canCompress()
    {
        $model = clone $this->getMixer()->getModel();

        $model->getState()->uuid = null; // Both ID and UUID are present on the query, and UUID points to the category ID

        return $model->fetch()->canDownload();
    }
}