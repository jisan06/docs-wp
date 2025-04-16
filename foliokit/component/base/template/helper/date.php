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

/**
 * Date Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperDate extends Library\TemplateHelperDate
{
    /**
     * Returns formatted date according to current local
     *
     * @param  array  $config An optional array with configuration options.
     * @return string Formatted date.
     */
    public function format($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

        $config->append([
            'date'     => 'now',
            'timezone' => true,
            'format'   => 'd F Y'
        ]);

        $timestamp = is_numeric($config->date) ? $config->date : strtotime($config->date);

        $format = 'Y-m-d H:i:s';

		$date = gmdate($format, $timestamp);

        $date = \EasyDocLabs\WP::get_date_from_gmt($date, $format); // Offsetted date based on WP timezone settings as required by ::date_i18n

        return \EasyDocLabs\WP::date_i18n($config->format, strtotime($date), $config->timezone);
    }
}
