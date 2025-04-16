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
 * Title bar Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Helper
 */
class TemplateHelperTitlebar extends TemplateHelperToolbar
{
    public function getToolbarType()
    {
        return 'actionbar';
    }

    /**
     * Render the action bar commands
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function render($config = array())
    {
        $config = new ObjectConfigJson($config);
        $config->append(array(
            'toolbar' => null,
            'title'   => null,
        ));

        //Set a custom title
        if($config->title) {
            $config->toolbar->setTitle($config->title);
        }

        $title = $this->getObject('translator')->translate($config->toolbar->getTitle());
        $html  = '';

        if (!empty($title))
        {
            $mobile = ($config->mobile === '' || $config->mobile) ? 'k-title-bar--mobile' : '';

            $html .= $this->buildElement('div', [
                'class' => 'k-title-bar k-js-title-bar '.$mobile
            ], $this->buildElement('div', ['class' => 'k-title-bar__heading'], $title));
        }

        return $html;
    }
}
