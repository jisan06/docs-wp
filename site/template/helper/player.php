<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Component\Files;
use EasyDocLabs\Library;
use EasyDocLabs\WP;

class TemplateHelperPlayer extends Files\TemplateHelperPlayer
{
    /**
     * @param array $config
     * @return string html
     */
    public function render($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'document' => null
        ]);

        $document = $config->document;
        
        $this->_insertToken($document);

        $html = '';

        if ($this->_isYoutube($document)) {
            $html = $this->_renderYoutube($document);
        }

        if ($this->_isVimeo($document)) {
            $html = $this->_renderVimeo($document);
        }

        if ($this->_isAudio($document)) {
            $html = $this->_renderAudio($document);
        }

        if ($this->_isVideo($document)) {
            $html = $this->_renderVideo($document);
        }

        return $html;
    }

    protected function _getControls($document)
    {
        $controls = ['play', 'progress', 'duration', 'mute', 'volume', 'fullscreen'];

        if ($document->isLocal() && $document->canDownload()) $controls[] = 'download';

        return $controls;
    }

    /**
     * @param $document
     * @return string
     */
    public function getVideoId($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'document' => null
        ]);

        $document = $config->document;

        if ($this->_isYoutube($document)) {
            return $this->_getYoutubeId($document);
        }

        if ($this->_isVimeo($document)) {
            return $this->_getVimeoId($document);
        }

        return '';
    }

    /**
     * @param $document
     * @return bool
     */
    protected function _isYoutube($document)
    {
        return $document->isYoutube();
    }

    /**
     * @param $document
     * @return string
     */
    protected function _getYoutubeId($document)
    {
        $url = parse_url($document->storage->path);

        if ($url)
        {
            if ($url['host'] === 'youtu.be') {
                return trim($url['path'], '/');
            }
            elseif (isset($url['query']))
            {
                parse_str($url['query'], $result);

                if (array_key_exists('v', $result)) {
                    return $result['v'];
                }
            }
        }

        return '';
    }

    /**
     * @param $document
     * @return string
     */
    protected function _renderYoutube($document)
    {
        $video_id = $this->_getYoutubeId($document);

        if ($video_id === '') {
            return '';
        }

        $controls = $this->_getControls($document);

        $html = $this->getTemplate()
                     ->render('com://site/easydoc/document/player_video_remote.html',
                     ['service' => 'youtube', 'id' => $video_id, 'document' => $document, 'controls' => $controls]);

        return $html;
    }

    /**
     * @param $document
     * @return bool
     */
    protected function _isVimeo($document)
    {
        return $document->isVimeo();
    }

    /**
     * @param $document
     * @return string
     */
    protected function _getVimeoId($document)
    {
        $video_id = substr(parse_url($document->storage->path, PHP_URL_PATH), 1);

        if ($video_id !== '') {
            return $video_id;
        }

        return '';
    }

    /**
     * @param $document
     * @return string
     */
    protected function _renderVimeo($document)
    {
        $id = substr(parse_url($document->storage->path, PHP_URL_PATH), 1);

        if ($id == '') {
            return '';
        }

        $controls = $this->_getControls($document);

        $html = $this->getTemplate()
                     ->render('com://site/easydoc/document/player_video_remote.html',
                     ['service' => 'vimeo', 'id' => $id, 'document' => $document, 'controls' => $controls]);

        return $html;
    }

    /**
     * @param $document
     * @return bool
     */
    protected function _isVideo($document)
    {
        if (in_array($document->extension, self::$_SUPPORTED_FORMATS['video'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $document
     * @return string
     */
    protected function _renderVideo($document)
    {
        $controls = $this->_getControls($document);

        $html = $this->getTemplate()
                     ->render('com://site/easydoc/document/player_video_local.html',
                     ['document' => $document, 'controls' => $controls]);

        return $html;
    }

    /**
     * @param $document
     * @return bool
     */
    protected function _isAudio($document)
    {
        if (in_array($document->extension, self::$_SUPPORTED_FORMATS['audio'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $document
     * @return string
     */
    protected function _renderAudio($document)
    {
        $controls = $this->_getControls($document);

        $html = $this->getTemplate()
                    ->render('com://site/easydoc/document/player_audio_local.html',
                    ['document' => $document, 'controls' => $controls]);

        return $html;
    }

    protected function _insertToken(Library\ModelEntityInterface $document)
    {
        if ($url = $document->download_link)
        {
            $url = $this->getObject('lib:http.url', ['url' => $url]);

            $token = $this->getObject('lib:http.token')
                        ->setClaim('entity', $document->uuid)
                        ->setClaim('origin', WP::get_home_url());

            $user = $this->getObject('user');

            if ($user->isAuthentic()) $token->setSubject($user->getEmail());
            
            $url->setQuery(array_merge(['auth_player' => $token->sign(WP::wp_salt('AUTH_SALT'))],$url->getQuery(true)));
    
            $document->download_link = $url->toString();
        }

        return $this;
    }
}