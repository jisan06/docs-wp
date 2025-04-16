<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

interface NotifierInterface
{
    public function notify(NotifierContextInterface $context);

    public function getActions();

    public function getPackage();

    public function getName();

    public function render($data = []);

    public function getData();

    public function getJob();
}
