<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('behavior.downloadlabel', ['params' => options()]); ?>

<? if (!isset($manage)) $manage = true ?>

<? if ($document->canDelete()): ?>
    <ktml:script src="assets://easydoc/site/js/items.js" />
<? endif; ?>

<? if (option('show_breadcrumb')): ?>
    <?= helper('breadcrumb.load', ['entity' => $document]) ?>
<? endif ?>

<div class="easydoc_document" itemscope itemtype="http://schema.org/CreativeWork">

    <? // Header ?>
    <? if (option('show_document_title')
          || ($document->canEdit() && $document->isLockable() && $document->isLocked())
          || (!$document->enabled)
          || (object('user')->getId() == $document->created_by)
    ): ?>
    <h<?= $heading; ?> class="koowa_header">
        <? // Header image ?>
        <? if ($document->icon && option('show_document_icon')): ?>
        <span class="koowa_header__item koowa_header__item--image_container">
            <? if (option('document_title_link') && $link): ?>
            <a class="koowa_header__image_link <?= option('document_title_link') === 'download' ? 'easydoc_track_download' : ''; ?>"
               <?= option('document_title_link') === 'download' ? 'type="'.$document->mimetype.'"' : ''; ?>
               href="<?= ($document->title_link) ?>"
               data-title="<?= escape($document->title); ?>"
               data-id="<?= $document->id; ?>"
                <?= option('download_in_blank_page') && option('document_title_link') === 'download' ? 'target="_blank"' : ''; ?>><!--
                -->
                <?= import('com://site/easydoc/document/icon.html', [
                    'icon'  => $document->icon,
                    'class' => ' k-icon--size-medium'.(strlen($document->extension) ? ' k-icon-type-'.$document->extension : '')
                ]) ?>
            </a>
            <? else: ?>
                <?= import('com://site/easydoc/document/icon.html', [
                    'icon'  => $document->icon,
                    'class' => ' k-icon--size-medium'.(strlen($document->extension) ? ' k-icon-type-'.$document->extension : '')
                ]) ?>
            <? endif; ?>
        </span>
        <? endif ?>

        <? // Header title ?>
        <span class="koowa_header__item">
            <span class="koowa_wrapped_content">
                <span class="whitespace_preserver">
                    <? if (option('show_document_title')): ?>
                        <? if (option('document_title_link') && $link): ?>
                        <a class="koowa_header__title_link <?= option('document_title_link') === 'download' ? 'easydoc_track_download' : ''; ?>"
                           <?= option('document_title_link') === 'download' ? 'type="'.$document->mimetype.'"' : ''; ?>
                           href="<?= ($document->title_link) ?>"
                           data-title="<?= escape($document->title); ?>"
                           data-id="<?= $document->id; ?>"
                           <?= option('download_in_blank_page') && option('document_title_link') === 'download' ? 'target="_blank"' : ''; ?>><!--
                            --><span itemprop="name"><?= escape($document->title); ?></span></a>
                        <? else: ?>
                            <span itemprop="name"><?= escape($document->title); ?></span>
                        <? endif; ?>
                    <? endif; ?>

                    <? // Show labels ?>

                    <? // Label locked ?>
                    <? if ($document->canEdit() && $document->isLockable() && $document->isLocked()): ?>
                        <span class="label label-warning"><?= helper('grid.lock_message', ['entity' => $document]); ?></span>
                    <? endif; ?>

                    <? // Label status ?>
                    <? if (!$document->enabled || $document->status !== 'published'): ?>
                        <? $status = $document->enabled ? translate($document->status) : translate('Draft'); ?>
                        &nbsp;<span class="label label-<?= $document->enabled ? $document->status : 'draft' ?>"><?= ucfirst($status); ?></span>
                    <? endif; ?>

                    <? // Label owner ?>
                    <? if (option('show_document_owner_label', 1) && object('user')->getId() == $document->created_by): ?>
                        <span class="label label-info"><?= translate('Owner'); ?></span>
                    <? endif; ?>

                    <? // Label new ?>
                    <? if (option('show_document_recent') && isRecent($document)): ?>
                        <span class="label label-success"><?= translate('New') ?></span>
                    <? endif; ?>

                    <? // Label popular ?>
                    <? if (option('show_document_popular') && ($document->hits >= option('hits_for_popular'))): ?>
                        <span class="label label-warning"><?= translate('Popular') ?></span>
                    <? endif; ?>
                </span>
            </span>
        </span>
    </h<?= $heading; ?>>
    <? endif; ?>

    <? // Dates&Owner ?>
    <? if ((option('show_document_created'))
        || ($document->modified_by && option('show_document_modified'))
        || (option('show_document_created_by'))
        || (option('show_document_category'))
        || (option('show_document_hits') && $document->hits)
    ): ?>
    <p class="easydoc_document_details">

        <? // Created ?>
        <? if (option('show_document_created')): ?>
        <span class="created-on-label">
            <time itemprop="datePublished" datetime="<?= $document->publish_date ?>">
                <?= translate('Published on'); ?> <?= helper('date.format', ['date' => $document->publish_date]); ?>
            </time>
        </span>
        <? endif; ?>

        <? // Modified ?>
        <? if (option('show_document_modified') && $document->modified_by): ?>
        <span class="modified-on-label">
            <time itemprop="dateModified" datetime="<?= $document->modified_on ?>">
                <?= translate('Modified on'); ?> <?= helper('date.format', ['date' => $document->modified_on]); ?>
            </time>
        </span>
        <? endif; ?>

        <? // Owner ?>
        <? if (option('show_document_created_by') && $document->created_by):
            $owner = '<span itemprop="author">'.$document->getAuthor()->getName().'</span>'; ?>
            <span class="owner-label">
                <?= translate('By {owner}', ['owner' => $owner]); ?>
            </span>
        <? endif; ?>

        <? // Category ?>
        <? if (option('show_document_category')):
            $category = '<span itemprop="genre">'.$document->category_title.'</span>'; ?>
            <span class="category-label">
                <?= translate('In {category}', ['category' => $category]); ?>
            </span>
        <? endif; ?>

        <? // Downloads ?>
        <? if (option('show_document_hits') && $document->hits): ?>
            <meta itemprop="interactionCount" content="UserDownloads:<?= $document->hits ?>">
            <span class="hits-label">
                <?= object('translator')->choose(['{number} download', '{number} downloads'], $document->hits, ['number' => $document->hits]) ?>
            </span>
        <? endif ?>
    </p>
    <? endif; ?>

    
    <? // Render audio/video player ?>
    <? if(!option('force_download') && option('show_player')): ?>
    <p>
        <?= $player = helper('player.render', ['document' => $document]) ?>
    </p>
    <? endif; ?>


    <? // Download area ?>
    <? if (empty($player) && $document->canDownload()): ?>
    <div class="easydoc_download<?php if ($document->description != '') echo " easydoc_download--right"; ?>">
        <a class="btn btn-large <?= $buttonstyle; ?> btn-block easydoc_download__button easydoc_track_download"
           type="<?= $document->mimetype ?>"
           href="<?= $document->download_link; ?>"
           data-title="<?= escape($document->title); ?>"
           data-id="<?= $document->id; ?>"
           <? if(!option('force_download')): ?>
           data-mimetype="<?= $document->mimetype ?>"
           data-extension="<?= $document->extension ?>"
           <? endif; ?>
           <?= (option('download_in_blank_page')) ? 'target="_blank"' : ''; ?>>

            <span class="easydoc_download_label">
              <?= translate('Download'); ?>
            </span>

            <? // Filetype and Filesize  ?>
            <? if ((option('show_document_size') && $document->size) || ($document->storage_type == 'file' && option('show_document_extension'))): ?>
                <span class="easydoc_download__info">(<!--
                --><? if ($document->storage_type == 'file' && option('show_document_extension')): ?><!--
                    --><?= escape($document->extension . (option('show_document_size') && $document->size ? ', ':'')) ?><!--
                --><? endif ?><!--
                --><? if (option('show_document_size') && $document->size): ?><!--
                    --><?= helper('string.humanize_filesize', ['size' => $document->size]) ?><!--
                --><? endif ?><!--
                -->)</span>
            <? endif; ?>
        </a>

        <? // Filename ?>
        <? if ($document->storage->name && option('show_document_filename')): ?>
            <p class="easydoc_download__filename" title="<?= escape($document->storage->name); ?>"><?= escape($document->storage->name); ?></p>
        <? endif; ?>
    </div>
    <? endif ?>

    <? // Document description ?>
    <? if (option('show_document_description') || option('show_document_image')): ?>
    <div class="easydoc_description">
        <? if (option('show_document_image') && $document->image): ?>
            <? if ($document->canDownload()): ?>
                <?= helper('behavior.thumbnail_modal'); ?>
                <a class="easydoc_thumbnail thumbnail" href="<?= $document->image_download_path ?>">
                    <img itemprop="thumbnailUrl" src="<?= $document->image_path ?>" alt="<?= escape($document->title); ?>" />
                </a>
            <? else: ?>
                <a class="easydoc_thumbnail thumbnail">
                    <img itemprop="thumbnailUrl" src="<?= $document->image_path ?>" alt="<?= escape($document->title); ?>" />
                </a>
            <? endif ?>
        <? endif ?>

        <? if (option('show_document_description')):
            $field = 'description_'.(isset($description) ? $description : 'full');
        ?>
            <div itemprop="description">
            <?= prepareText($document->$field); ?>
            </div>
        <? endif; ?>
    </div>
    <? endif ?>

    <? if ($manage): ?>
        <? // Edit area | Import partial template from document view ?>
        <?= import('com://site/easydoc/document/manage.html', [
            'document' => $document,
            'button_size' => 'small',
        ]) ?>
    <? endif ?>

</div>
