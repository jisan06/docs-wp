<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>
<?= helper('behavior.jquery'); ?>
<?= helper('behavior.modal'); ?>
<?= helper('gallery.load', ['params' => options()]); ?>

<?= helper('com://admin/easydoc.behavior.photoswipe'); ?>


<? if( count($documents)): ?>

    <div class="koowa_media--gallery">

        <div class="koowa_media_wrapper koowa_media_wrapper--documents">
            <div class="koowa_media_contents">
                <?php // this comment below must stay ?>
                <div class="koowa_media"><!--
                        <? $count = 0; ?>
                        <? foreach ($documents as $document): ?>
                     --><div class="koowa_media__item" itemscope itemtype="http://schema.org/ImageObject">
                        <div class="koowa_media__item__content document">
                            <?= import('com://site/easydoc/document/gallery.html', array(
                                'document' => $document,
                                'count'    => $count
                            )) ?>
                        </div>
                    </div><!--
                        <? $count++; ?>
                        <? endforeach ?>
                 --></div>
            </div>
        </div>
    </div>

<? endif; ?>
