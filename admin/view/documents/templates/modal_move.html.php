<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<div id="document-move-modal" class="k-ui-namespace k-small-inline-modal-holder mfp-hide">
    <div class="k-inline-modal">
        <form class="k-js-move-form">

            <h3 class="k-inline-modal__title">
                <?= translate('Move to') ?>
            </h3>

            <div class="k-form-group">
                <?= helper('listbox.categories', [
                    'deselect'    => true,
                    'permissions' => 'upload_document',
                    'attribs'     => ['id' => 'document_move_target'],
                    'selected'    => null
                ]) ?>
            </div>

            <div class="k-form-group">
                <button class="k-button k-button--primary" disabled ><?= translate('Move'); ?></button>
            </div>

        </form>
    </div>
</div>
