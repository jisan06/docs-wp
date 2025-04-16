<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Translator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class Translator extends Library\Translator
{
    public static $wordpress_strings = [
        'all', 'title', 'status', 'uncategorized', 'details', 'description',
        'apply', 'save', 'cancel', 'close', 'remove',
        'public', 'private',
        'published', 'unpublished', 'enabled', 'disabled',
        'yes', 'no',
        'previous', 'next', 'today', 'time', 'date',
        'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun',
        'january', 'february', 'march', 'april', 'may', 'june',
        'july', 'august', 'september', 'october', 'november', 'december'
    ];

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'language' => $this->_normalizeLocale(\EasyDocLabs\WP::get_user_locale()),
        ]);

        parent::_initialize($config);
    }

    public function load($url, $override = false)
    {
        if (!$this->isLoaded($url))
        {
            parent::load($url, $override);

            $overrides = [];

            // Handle translation overrides

            foreach (array_unique($this->findOverrides($url)) as $file)
            {
                try {
                    $loaded = $this->getObject('object.config.factory')->fromFile($file)->toArray();
                } catch (\Exception $e) {
                    return false;
                }

                $overrides = array_merge($overrides, $loaded);
            }

            $this->getCatalogue()->add($overrides, true);
        }

        return true;
    }

   /**
     * Find translation overrides from a url
     *
     * @param string $path The language path
     * @return array An array with physical file paths
     */
    public function findOverrides($path)
    {
        $language = $this->getLanguage();
        $fallback = $this->getLanguageFallback();
        $locator  = $this->getObject('translator.locator.factory')->createLocator($path);

        $results = [];

        if ($locator instanceof TranslatorLocatorOverrides)
        {
            // Try to load the fallback translations overrides first
            if ($fallback && $fallback != $language)
            {
                if ($result = $locator->locateOverrides($path .'/'.$fallback.'/'.$fallback.'.*')) {
                    $results[] = $result;
                } elseif (($pos = strpos($fallback, '-')) !== false) {
                    $fallback = substr($fallback, 0, $pos);

                    if ($result = $locator->locateOverrides($path .'/'.$fallback.'/'.$fallback.'.*')) {
                        $results[] = $result;
                    }
                }
            }

            // Find translations overrides based on the language
            if ($result = $locator->locateOverrides($path .'/'.$language.'/'.$language.'.*')) {
                $results[] = $result;
            } elseif (($pos = strpos($language, '-')) !== false) {
                $language = substr($language, 0, $pos);

                if ($result = $locator->locateOverrides($path .'/'.$language.'/'.$language.'.*')) {
                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    /**
     * Loads the Foliokit base language files
     *
     * @param string $language
     * @return Library\TranslatorAbstract
     */
    public function setLanguage($language)
    {
        $result = parent::setLanguage($language);

        $this->load('com:base');

        return $result;
    }

    public function translate($string, array $parameters = array())
    {
        if (in_array(strtolower($string), static::$wordpress_strings)) {
            $translation = \EasyDocLabs\WP::__(ucfirst($string));

            if ($this->isDebug()) {
                $translation = '👌'.$translation.'👌';
            }
        } else {
            $translation = parent::translate($string, $parameters);
        }

        return $translation;
    }

    /**
     * Generate translation key
     *
     * @param string $string String to be translated
     * @return string Key for the translation file
     */
    public function generateKey($string)
    {
        static $key_cache = [];
        $key   = strtolower($string);
        $limit = 40;

        if(!isset($key_cache[$string]))
        {
            if (!$limit || strlen($key) <= $limit)
            {
                $key = strip_tags($key);
                $key = preg_replace('#\s+#m', ' ', $key);
                $key = preg_replace('#\{([A-Za-z0-9_\-\.]+)\}#', '$1', $key);
                $key = preg_replace('#(%[^%|^\s|^\b]+)#', 'x', $key);
                $key = preg_replace('#&.*?;#', '', $key);
                $key = preg_replace('#[\s-]+#', ' ', $key);
                $key = preg_replace('#[^A-Za-z0-9_ ]#', '', $key);
                $key = preg_replace('#_+#', ' ', $key);
                $key = trim($key);
                $key = strtolower($key);
            }
            else
            {
                $hash = substr(md5($key), 0, 5);
                $key  = $this->generateKey(substr($string, 0, $limit));
                $key .= ' '.$hash;
            }

            $key_cache[$string] = $key;
        }

        return $key_cache[$string];
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
