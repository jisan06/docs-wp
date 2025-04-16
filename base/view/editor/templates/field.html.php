<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die;

if (empty($content)) { $content = ''; }
?>

<script data-inline>
    (function()
    {
        let description = <?= \EasyDocLabs\WP::wp_json_encode($content); ?>;
        const origin = window.location.origin;

        descriptionField = document.createElement('textarea');
        descriptionField.setAttribute('name', 'description');
        descriptionField.hidden = true;
        descriptionField.value = description;

        window.addEventListener('message', function(event)
        {
            if (event.origin !== origin || !event.data.publisher || event.data.publisher !== 'easy-docs-ait/editor/description') {
                return;
            }

            if (event.data.event === 'ready')
            {
                const iframe = document.querySelector('.easydoc-editor').contentWindow;

                iframe.postMessage({
                    event: 'set-content',
                    content: description
                }, origin);

                kQuery('.k-js-form-controller').append(descriptionField);
            }

            if (event.data.event === 'content-change') {
                descriptionField.value = event.data.content;
            }
        });
    })();
</script>
<div class="k-form-group">
    <iframe src="<?= route('view=editor&layout=default'); ?>" class="easydoc-editor" style="width: 100%; min-height: 700px; border: 0"></iframe>
</div>
