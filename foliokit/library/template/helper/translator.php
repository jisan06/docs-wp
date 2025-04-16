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
 * Translator Helper
 *
 * Adds translation keys used in JavaScript to the translator object
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Library\Template\Helper
 */
class TemplateHelperTranslator extends TemplateHelperAbstract
{
    public function script($config = array())
    {
        $config = new ObjectConfigJson($config);
        $config->append(array(
            'strings' => array()
        ));

        $strings    = ObjectConfig::unbox($config->strings);
        $translator = $this->getObject('translator');

        $translations = array();
        foreach ($strings as $string) {
            $translations[$string] = $translator->translate($string);
        }

        $html  = '';
        $html .= $this->createHelper('behavior')->foliokit();
        $html .= $this->buildElement('script', [], "
            if(!Foliokit) {
                var Foliokit = typeof Koowa !== 'undefined' ?  Koowa : {};
            }
            
            if (typeof Foliokit.translator === 'object' && Foliokit.translator !== null) {
                Foliokit.translator.loadTranslations(".json_encode($translations).");
            }");

        return $html;
    }
}
