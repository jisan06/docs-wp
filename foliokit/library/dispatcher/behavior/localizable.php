<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Localizable Dispatcher Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Behavior
 */
class DispatcherBehaviorLocalizable extends DispatcherBehaviorAbstract
{
    /**
     * Load the language
     *
     * @param   ViewContextInterface $context A view context object
     * @return  void
     */
    protected function _beforeDispatch(DispatcherContext $context)
    {
        $context->getSubject()->loadLanguage();
    }

    /**
     * Get the language
     *
     * Returns a properly formatted language tag, eg xx-XX
     * @link https://en.wikipedia.org/wiki/IETF_language_tag
     * @link https://tools.ietf.org/html/rfc5646
     *
     * @return string|null The language tag
     */
    public function getLanguage()
    {
        return $this->getObject('translator')->getLanguage();
    }

    /**
     * Load the language
     *
     * @return 	void
     */
    public function loadLanguage()
    {
        $package = $this->getIdentifier()->package;
        $domain  = $this->getIdentifier()->domain;

        $urls = ['com:'.$package];

        if ($domain) {
            // Load first to override base translations
            array_unshift($urls, 'com://'.$domain.'/'.$package);
        }

        foreach ($urls as $url) {
            $this->getObject('translator')->load($url);
        }
    }

}