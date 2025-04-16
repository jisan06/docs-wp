<?php
/**
 * FolioKit Scheduler
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
namespace EasyDocLabs\Component\Scheduler;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Job behavior
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Scheduler
 */
class DispatcherBehaviorSchedulable extends Library\DispatcherBehaviorAbstract
{
    public static function getLogPath() {
        return WP\CONTENT_DIR.'/easydoclabs/logs/scheduler.php';
    }

    protected function _beforeInit(Library\DispatcherContext $context)
    {
        WP::add_action( 'wp_footer', function() {
            if ($this->_canRun()) {
                $url = WP::get_home_url().'/index.php?component=foliokit&controller=scheduler';

                /*
                 * To recreate this block:
                 * * Compress request.js
                 * * Remove the first block for data-scheduler property and replace with a direct call
                 */
                $html = '<script type="text/javascript">/*EasyDocLabs cronjob scheduler*/
!function(){function e(e,t,n,o){try{o=new(this.XMLHttpRequest||ActiveXObject)("MSXML2.XMLHTTP.3.0"),o.open("POST",e,1),o.setRequestHeader("X-Requested-With","XMLHttpRequest"),o.setRequestHeader("Content-type","application/x-www-form-urlencoded"),o.onreadystatechange=function(){o.readyState>3&&t&&t(o.responseText,o)},o.send(n)}catch(c){}}function t(n){e(n,function(e,o){try{if(200==o.status){var c=JSON.parse(e)
"object"==typeof c&&c["continue"]&&setTimeout(function(){t(n)},1e3)}}catch(u){}})}t("'.$url.'")}()</script>';

                echo $html;
            }
        });

        $query = $this->getRequest()->getQuery();

        if ($query->component === 'foliokit' && $query->controller === 'scheduler') {
            $this->getIdentifier('com:scheduler.controller.dispatcher')->getConfig()->append([
                'behaviors' => [
                    'com:scheduler.controller.behavior.loggable' => [
                        'log_file' => static::getLogPath()
                    ]
                ]
            ]);

            $this->getObject('com:scheduler.dispatcher')->dispatch();
        }
    }

    /**
     * @return bool
     */
    protected function _canRun()
    {
        if (@$_SERVER['REQUEST_METHOD'] === 'GET' && !WP::wp_installing()) {
            $wpdb = WP::global('wpdb');

            $now         = gmdate('Y-m-d H:i:s');
            $query       = /** @lang text */
                "SELECT sleep_until < '$now' FROM {$wpdb->prefix}scheduler_metadata WHERE type = 'metadata' LIMIT 1";
            $sleep_until = $wpdb->get_var($query);

            // null = no rows or actual boolean value
            if ($sleep_until === null || $sleep_until) {
                return true;
            }
        }

        return false;
    }
}