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
use EasyDocLabs\WP;

/**
 * Dispatcher Request
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
final class DispatcherRequest extends Library\DispatcherRequest
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'language' => WP::get_user_locale(),
            'timezone' => WP::get_option('timezone_string') ?: 'UTC'
        ]);

        parent::_initialize($config);
    }

    /**
     * Returns the site URL from which this request is executed.
     *
     * @return  Library\HttpUrl  A HttpUrl object
     */
    public function getSiteUrl()
    {
        $url = clone $this->getBaseUrl();

        if(\EasyDocLabs\WP::is_admin()) {
            $url->setPath(str_ireplace('/wp-admin', '', $url->getPath()));
        }

        return $url;
    }

    /**
     * If PHP is on a secure connection always return 443 instead of 80
     *
     * When PHP is behind a reverse proxy port information might not be forwarded correctly.
     * Also, $_SERVER['SERVER_PORT'] is not configured correctly on some hosts and always returns 80.
     *
     * {@inheritdoc}
     */
    public function getPort()
    {
        $port = parent::getPort();

        if ($this->isSecure() && in_array($port, ['80', '8080'])) {
            $port = '443';
        }

        return $port;
    }

    public function getLanguage()
    {
        if(!$language = $this->getUser()->getLanguage()) {
            $language = $this->getConfig()->language;
        }

        return $this->_normalizeLocale($language);
    }

    public function getTimezone()
    {
        if(!$timezone = $this->getUser()->getTimezone()) {
            $timezone = $this->getConfig()->timezone;
        }

        return $timezone;
    }

    /**
     * Returns the Wordpress locale in the xx-XX format
     *
     * @param $locale
     * @return string|null
     */
    protected function _normalizeLocale($locale)
    {
        $locale = preg_split('#[\-_\s\.]+#i', $locale);

        if ($locale && count($locale) === 1) { // Convert es to es-ES
            $locale = sprintf('%s-%s', $locale[0], strtoupper($locale[0]));
        }
        elseif ($locale && count($locale) >= 2) { // Convert en_US_posix to en-US
            $locale = sprintf('%s-%s', $locale[0], strtoupper($locale[1]));
        }
        else {
            $locale = null;
        }

        return $locale;
    }
}