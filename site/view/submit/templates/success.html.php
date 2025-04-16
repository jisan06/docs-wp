<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>


<? // Header ?>
<h2><?= translate('Thank you for your submission.'); ?></h2>


<? // Message ?>
<? if (!option('auto_publish')): ?>
    <p>
        <?= translate('Your submission will be reviewed first before getting published.'); ?>
    </p>
<? endif; ?>