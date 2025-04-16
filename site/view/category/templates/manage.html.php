<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? $redirect     = isset($redirect) ? $redirect : 'referrer';
$button_size     = 'btn-' . (isset($button_size) ? $button_size : 'small');
$parentOpen      = isset($parent) ? '<' . $parent . (isset($parentClass) ? ' class="' . $parentClass . '"' : '') . '>' : '<p>';
$parentClose     = isset($parent) ? '</' . $parent . '>' : '</p>';
$empty           = empty($category->_documents_count); ?>

<? // Edit and delete buttons ?>

<? if (!($category->isLockable() && $category->isLocked()) && ($category->canEdit() || $category->canDelete())): ?>

    <?= $parentOpen ?>

    <? // Edit ?>

    <? if ($category->canEdit()): ?>
        <a class="btn btn-default <?= $button_size ?>"
           href="<?= route($category, ['view' => 'category', 'layout' => 'form', 'options' => $query_options]) ?>">
            <?= translate('Edit'); ?>
        </a>
    <? endif ?>

    <? // Delete ?>

    <? if ($category->canDelete()): ?>

        <? $data = [
            'method' => 'post',
            'url' => (string) route($category, ['view' => 'category', 'layout' => 'form']),
            'params' => [
                '_method'    => 'delete',
                '_referrer'  => base64_encode(option('request')->url)
            ]
        ];
        
        if ($redirect === 'referrer')
        {
            if ($referrer = option('request')->referrer) {
                $data['params']['_referrer'] = base64_encode($referrer);
            } else {
                $data['params']['_referrer'] = base64_encode(option('site')->url);
            }
        } ?>

        <?= helper('behavior.deletable', ['item' => 'category']); ?>

        <a class="btn <?= $button_size ?> btn-danger easydoc-deletable-category <?= !$empty ? 'k-is-disabled disabled' : '' ?>" rel="<?= escape(\EasyDocLabs\WP::wp_json_encode($data)) ?>" <? if ($bind_listener): ?> data-documents-count="<?= (int) $category->_documents_count ?>" <? endif ?>>
            <?= translate('Delete') ?>
        </a>

    <? endif ?>

    <?= $parentClose ?>

<? endif ?>
