<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
namespace EasyDocLabs\Component\Base;
 
/**
 * Overrides Translator Locator Interface
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Translator\Locator
 */
interface TranslatorLocatorOverrides
{
    /**
     * Get the list of overrides templates
     *
     * @param  string $url The language url
     * @return array The overrides templates
     */
    public function getOverridesTemplates($url);

    /**
     * Locate the resource overrides based on a url
     *
     * @param  string $url  The resource url
     * @return string|false  The physical overrides path for the resource or FALSE if the url cannot be located
     */
    public function locateOverrides($url);
}