<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

/**
 * Serves a file to the user
 */
class ControllerBehaviorPreviewable extends Library\ControllerBehaviorEditable
{
    protected static $_gdocs_extensions = [
        'ogg', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pages', 'ai',
        'psd', 'tiff', 'dxf', 'svg', 'eps', 'ps', 'ttf', 'xps'
    ];

    public function getGooglePreviewExtensions()
    {
        return static::$_gdocs_extensions;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\CommandHandlerInterface::PRIORITY_HIGH
        ]);

        parent::_initialize($config);
    }

    protected function _beforeRender(Library\ControllerContext $context)
    {
        if ($this->canPreview() && $this->canRead())
        {
            $secret = \EasyDocLabs\WP::wp_salt('AUTH_SALT');
            $token  = $this->getObject('lib:http.token')->setSubject($context->user->getEmail());

            $url = clone $this->getObject('request')->getUrl();
            $url->query['force_download'] = 1;

            //If the user is logged in add the authentication token
            if($context->user->isAuthentic()) {
                $url->query['auth_token'] = $token->sign($secret);
            }

            $redirect = sprintf('https://docs.google.com/viewer?url=%s', urlencode($url));

            $context->response->setRedirect($redirect);
            return false;
        }
    }

    /**
     * Returns true if Google viewer is enabled and works for the current document type
     *
     * @return bool
     */
    public function canPreview()
    {
        $result   = false;
        $document = $this->getModel()->fetch();

        if ($document instanceof Library\ModelEntityInterface && !$document->isNew())
        {
            $options = $this->getOptions();

            $options->append(['force_download' => true, 'preview_with_gdocs' => true]);

            if (!$options->force_download && $options->preview_with_gdocs)
            {
                if (!$this->getRequest()->query->has('force_download') && $document->storage_type === 'file')
                {
                    if (in_array($document->storage->extension, self::$_gdocs_extensions)) {
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }
}