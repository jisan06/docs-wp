<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

/**
 * Thumbnails Json View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ViewThumbnailsJson extends ViewJson
{
    protected function _renderData()
    {
        $list = $this->getModel()->fetch();
        $results = [];
        foreach ($list as $item) 
        {
        	$key = $item->filename;
        	$results[$key] = $item->toArray();
        }
        ksort($results);

    	$output = parent::_renderData();
        $output['items'] = $results;
        $output['total'] = count($list);

        return $output;
    }
}
