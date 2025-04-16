<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Activity Template Helper.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class TemplateHelperActivity extends Library\TemplateHelperAbstract implements Library\ObjectMultiton, ActivityRendererInterface
{
    /**
     * Renders an activity.
     *
     * Wraps around {@link render()} to easily render activities on layouts.
     *
     * @param array $config An optional configuration array.
     * @return string The rendered activity.
     */
    public function activity($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $output = '';

        if ($activity = $config->entity) {
            $output = $this->render($activity, $config);
        }

        return $output;
    }

    /**
     * Renders an activity.
     *
     * @param ActivityInterface $activity The activity object.
     * @param array                          $config   An optional configuration array.
     * @return string The rendered activity.
     */
    public function render(ActivityInterface $activity, $config = [])
    {
        $config = new Library\ObjectConfig($config);

        $translator = $activity->getTranslator();

        $output = $activity->getActivityFormat();

        if (preg_match_all('/{(.*?)}/', $output, $labels))
        {
            $tokens = $translator->getActivityTokens($activity);

            foreach ($labels[1] as $label)
            {
                $parts = explode(':', $label);

                if (isset($tokens[$parts[0]]))
                {
                    $token = $tokens[$parts[0]];

                    $object = clone $token;

                    // Deal with context translations.
                    if (!isset($parts[1]))
                    {
                        if ($object->isTranslatable()) {
                            $object->setDisplayName($translator->translateActivityToken($object, $activity));
                        }
                    }
                    else $object->setDisplayName($parts[1]);

                    if ($object = $this->_renderObject($object, $config)) {
                        $output = str_replace('{' . $label . '}', $object, $output);
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Renders an activity object.
     *
     * @param ActivityObjectInterface $object The activity object.
     * @param Library\ObjectConfig                 $config The configuration object.
     * @return string The rendered object.
     */
    protected function _renderObject(ActivityObjectInterface $object, Library\ObjectConfig $config)
    {
        $config->append(['html' => true, 'escaped_urls' => true, 'fqr' => false, 'links' => true]);

        if ($output = $object->getDisplayName())
        {
            if ($config->html)
            {
                $output  = $object->getDisplayName();
                $attribs = $object->getAttributes() ? $this->buildAttributes($object->getAttributes()) : '';

                if ($config->links && $url = $object->getUrl())
                {
                    // Make sure we have a fully qualified route.
                    if ($config->fqr && !$url->getHost()) {
                        $url->setUrl($this->getTemplate()->url()->toString(Library\HttpUrl::AUTHORITY));
                    }

                    $url    = $url->toString(KHttpUrl::FULL, $config->escaped_urls);
                    $output = "<a {$attribs} href=\"{$url}\">{$output}</a>";
                }
                else $output = "<span {$attribs}>{$output}</span>";
            }
        }
        else $output = '';

        return $output;
    }
}
