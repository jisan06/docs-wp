<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die;

$show_delete         = $document->canDelete();
$show_edit           = $document->canEdit();
$show_action_buttons = $show_action_buttons ?? true;

$button_size  = 'btn-'.(isset($button_size) ? $button_size : 'small');
?>

<? // Edit and delete buttons ?>
<? if ($show_action_buttons && !($document->isLockable() && $document->isLocked()) && ($show_edit || $show_delete)): ?>

    <div class="btn-toolbar koowa_toolbar">

        <div class="btn-group">

        <? // Edit ?>
        <? if ($show_edit): ?>
            <a class="btn btn-<?= $button_size ?>" href="<?= route($document, 'layout=form&options=' . $query_options)?>"><?= translate('Edit'); ?></a>
        <? endif ?>

        <? // Delete ?>
        <? if ($show_delete):
        
            $data = [
                'method' => 'post',
                'url'    => (string) route($document, ['layout' => 'form']),
                'params' => [
                    '_method'    => 'delete',
                    '_referrer'  => base64_encode(option('request')->url)
                ]
            ];

            if (parameters()->view == 'document')
            {
                $referrer = option('request')->referrer;

                if ($referrer) {
                    $data['params']['_referrer'] = base64_encode($referrer);
                } else {
                    $data['params']['_referrer'] = base64_encode(option('site')->url);
                }
            }
        ?>

            <a class="btn btn-<?= $button_size ?> btn-danger" data-action="delete-item" href="#" rel="<?= escape(\EasyDocLabs\WP::wp_json_encode($data)) ?>"
                <?= parameters()->view == 'document' || parameters()->layout === 'default' || parameters()->layout === 'list' ? 'data-ajax="false"' : '' ?>
                data-url="<?= escape($data['url']) ?>"
                data-params="<?= escape(\EasyDocLabs\WP::wp_json_encode($data['params'])) ?>"
                data-prompt="<?= escape(translate('You will not be able to bring this item back if you delete it. Would you like to continue?', ['item' => strtolower(translate('document'))])) ?>"
                data-document="<?= $document->uuid ?>">

                <?= translate('Delete') ?>

            </a>
        
            <? endif ?>

        </div>

    </div>

<? endif ?>
