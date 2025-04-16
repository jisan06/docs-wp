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

class ControllerLicense extends ControllerModel
{
    /** @var License */
    protected $_license;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        /** @var License $license */
        $this->_license = $this->getObject('license');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'model' => 'lib:model.empty',
            'formats' => ['json']
        ]);

        parent::_initialize($config);
    }

    protected function _authenticate()
    {
        $query = $this->getRequest()->getQuery();

        $signature = $query->get('signature', 'string');

        if (!$signature) {
            return false;
        }

        $postdata = file_get_contents("php://input");

        try {
            $public_key = $this->_license->getPublicKey();

            // Load required libraries
            $this->getObject('com:easydoc.crypto.token');

            $rsa = new \Ait Theme Club\RSA\Crypt_RSA();
            $rsa->loadKey($public_key);
            $rsa->setSignatureMode(JT_CRYPT_RSA_SIGNATURE_PKCS1);

            if (!$rsa->verify($postdata, $this->_fromBase64url($signature))) {
                return false;
            }

            $timestamp = $this->getRequest()->getData()->get('timestamp', 'int');

            // Only allow requests signed in the last (or next since server clocks might go haywire) 15 minutes
            if (!$timestamp || abs(time() - $timestamp) > 900) {
                return false;
            }
        }
        catch (\Exception $e) {
            return false;
        }

    }

    public function canInfo()
    {
        return $this->_authenticate();
    }

    public function canUpdate()
    {
        return $this->_authenticate();
    }

    public function canRefresh()
    {
        return $this->_authenticate();
    }

    protected function _actionInfo(Library\ControllerContext $context)
    {
        return new Library\ObjectConfigJson($this->_license->getSiteData());
    }

    protected function _actionUpdate(Library\ControllerContext $context)
    {
        $data = $context->getRequest()->getData();

        if ($data->public_key) {
            $this->_license->setPublicKey($data->public_key);
        }

        if ($data->license) {
            $this->_license->setLicense($data->license);
        }

        if ($data->site_key) {
            $this->_license->setSiteKey($data->site_key);
        }

        if ($data->api_key) {
            $this->_license->setApiKey($data->api_key);
        }

        return $this->_actionInfo($context);
    }

    protected function _actionRefresh(Library\ControllerContext $context)
    {
        /** @var License $license */
        $license = $this->getObject('license');
        $license->refresh();

        return $this->_actionInfo($context);
    }

    protected function _fromBase64url($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder)
        {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }
}
