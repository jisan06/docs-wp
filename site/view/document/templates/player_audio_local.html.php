<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2012 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('player.load', ['selector' => '.easydoc_player']); ?>

<audio class="easydoc_player"
    data-media-id="<?= $document->id ?>"
    data-plyr-config="<?= htmlentities(\EasyDocLabs\WP::wp_json_encode(['controls' => $controls])) ?>"
    data-title="<?= escape($document->title) ?>"
    data-category="easydoc"
    preload="none"
    controls>
    <source src="<?= $document->download_link ?>" type="audio/<?= $document->extension ?>" />
</audio>
