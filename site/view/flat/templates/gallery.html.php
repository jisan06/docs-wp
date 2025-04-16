<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? // Document list | Import child template from documents view ?>
<?= import('com://site/easydoc/documents/gallery.html', [
    'documents' => $documents,
    'photoswipe' => $can_download,
    'subcategories' => []
])?>
