<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die;

wp_editor('', 'editor', [
    'media_buttons' => true,
    'drag_drop_upload' => true,

] ); ?>
<style>
    .update-nag, .notice {
        display: none;
    }
</style>
<script>
    (function() {
        document.body.classList.remove('folded', 'auto-fold');

        let resizeEditor = function() {
            if (tinymce.activeEditor) {
                let targetHeight = window.innerHeight; // Change this to the height of your wrapper element
                let mce_bars_height = 0;

                document.querySelectorAll('.mce-toolbar, .mce-statusbar, .mce-menubar').forEach(function(el) {
                    mce_bars_height += el.offsetHeight;
                });

                // -34 for the top media button, -20 for margins etc
                tinymce.activeEditor.theme.resizeTo('100%', targetHeight - mce_bars_height - 20 - 34);
            }
        }

        const payload = {
            publisher: <?= \EasyDocLabs\WP::wp_json_encode($publisher) ?>,
        };
        const origin = window.location.origin;


        const getActiveTinyMCE = () =>
        {
            return tinymce.activeEditor;
        }

        const getQuicktagsEditor = () =>
        {
            return document.querySelector('#editor');
        }

        const getActiveEditorContent = () =>
        {
            let content, editor;

            if (editor = getActiveTinyMCE()) {
                content = editor.getContent();
            } else if (editor = getQuicktagsEditor()) {
                content = editor.value;
            }

            return content;
        };

        const setActiveEditorContent = (content) =>
        {
            let editor;

            if (editor = getActiveTinyMCE()) {
                editor.setContent(content);
            } else if (editor = getQuicktagsEditor()) {
                editor.value = content;
            }
        };

        const postContentChange = (content) => {
            window.parent.postMessage({...payload, event: 'content-change', content: content}, origin);
        };

        window.addEventListener('message', function(event)
        {
            if (event.data.event === 'set-content'  && event.data.content) {
                setActiveEditorContent(event.data.content);
            }

            if (event.data.event === 'get-content') {
                postContentChange(getActiveEditorContent());
            }
        });

        window.addEventListener('load', function() {
            resizeEditor();

            function debounceEvent(callback, time) {
                let interval;
                return (...args) => {
                    clearTimeout(interval);
                    interval = setTimeout(() => {
                        interval = null;
                        callback(...args);
                    }, time);
                };
            }

            window.parent.postMessage({...payload, event: 'ready'}, origin);

            const setTinyMCE = (bind_switch = true) =>
            {
                let i = 0, loop = setInterval(function()
                {
                    i++;

                    // TinyMCE

                    let editor = getActiveTinyMCE()

                    if (editor)
                    {
                        editor.on('Change', debounceEvent(() => {
                            postContentChange(editor.getContent());
                        }, 200));

                        editor.on('input', debounceEvent(() => {
                            postContentChange(editor.getContent());
                        }, 200));

                        clearInterval(loop);
                    }

                    if (i === 50)
                    {
                        clearInterval(loop);

                        if (bind_switch) {
                            document.querySelector('#editor-tmce').addEventListener('click', () => setTinyMCE(false), {once: true});
                        }
                    }
                }, 50);
            };

            setTinyMCE();
            
            // QuickTags editor

            let editor = getQuicktagsEditor()

            if (editor)
            {
                editor.addEventListener("change", debounceEvent(() => {
                    postContentChange(editor.value);
                }, 200));

                editor.addEventListener("input", debounceEvent(() => {
                    postContentChange(editor.value);
                }, 200));
            }
        });

        let timeout;
        window.addEventListener('resize', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                resizeEditor();
            }, 250);
        })
    })();

</script>
