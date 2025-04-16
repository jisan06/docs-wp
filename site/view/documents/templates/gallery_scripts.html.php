<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<ktml:script src="assets://easydoc/site/js/gallery.js" />
<ktml:script src="assets://easydoc/site/js/items.js" />

<script>
    kQuery(function($) {

        var documentsGallery = $('.koowa_media_wrapper--documents'),
            categoriesGallery = $('.koowa_media_wrapper--categories'),
            itemWidth = parseInt($('.koowa_media_wrapper--documents .koowa_media__item').css('width'));

        if ( categoriesGallery ) {
            categoriesGallery.simpleGallery({
                item: {
                    'width': itemWidth
                }
            });
        }

        if ( documentsGallery ) {
            documentsGallery.simpleGallery({
                item: {
                    'width': itemWidth
                }
            });
        }
    });
</script>

<? if (!empty($track_downloads)): ?>
    <?= helper('com://site/easydoc.behavior.download_tracker'); ?>
<? endif; ?>