<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;
use EasyDocLabs\EasyDoc\DatabaseBehaviorPermissible;
use EasyDocLabs\Library;

class ControllerConfig extends Base\ControllerModel
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.save', '_setRedirect');
        $this->addCommandCallback('after.apply', '_setRedirect');
        $this->addCommandCallback('after.cancel', '_setRedirect');
        $this->addCommandCallback('before.render', '_checkThumbnailsSupport');
        $this->addCommandCallback('before.save', '_checkPermissionsChange');
        $this->addCommandCallback('before.apply', '_checkPermissionsChange');
    }

    /**
     * We always need to call edit since config is never new
     */
    protected function _actionAdd(Library\ControllerContext $context)
    {
        return $this->_actionEdit($context);
    }

    protected function _actionClear_cache(Library\ControllerContext $context)
    {
        $this->getObject('com:easydoc.model.entity.config')->clearCache();

        return new Library\ObjectConfigJson([
            'success' => true
        ]);
    }

    protected function _actionRefresh_license(Library\ControllerContext $context)
    {
        $params = [
            'license' => isset($_POST['license']) ? $_POST['license'] : '',
        ];
        $this->getObject('license')->refresh($params);

        return new Library\ObjectConfigJson([
            'success' => true
        ]);
    }

    /**
     * Avoid getting redirected to the configs, export, or import views
     */
    protected function _setRedirect(Library\ControllerContextInterface $context)
    {
        $response = $context->getResponse();

        if ($response->isRedirect())
        {
            $url = $response->getHeaders()->get('Location');
            if (preg_match('#view=(configs|export|import)#', $url, $matches)) {
                $toUrl = str_replace('view='.$matches[1], 'view=documents', $url);
                $toUrl = str_replace('layout=debug', 'layout=default', $toUrl);
                $response->setRedirect($toUrl);
            }
        }
    }

    protected function _checkThumbnailsSupport(Library\ControllerContextInterface $context)
    {
        $thumbnails_available = $this->getObject('com:easydoc.model.entity.config')->thumbnailsAvailable();

        $this->getView()->thumbnails_available = $thumbnails_available;

        if (!$thumbnails_available)
        {
            $message = $this->getObject('translator')->translate('GD missing');
            $this->getResponse()->addMessage($message, 'warning');
        }
    }

    protected function _checkPermissionsChange(Library\ControllerContextInterface $context)
    {
        $data = $context->getRequest()->getData();

        // Ignore checks when saving settings on debug context (aka debug layout)

        if (!isset($data->_context) || $data->_context != 'debug')
        {
            $config = $this->getModel()->fetch();

            if (!isset($data->permissions)) {
                $data->permissions = [];
            }

            // Compare current and new permissions

            if (DatabaseBehaviorPermissible::serialize($config->permissions) !== DatabaseBehaviorPermissible::serialize($data->permissions)) {
                $this->getObject('easydoc.users')->clearPermissions();
            }
        }
    }
}
