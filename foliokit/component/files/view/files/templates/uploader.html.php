<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
defined('FOLIOKIT') or die; ?>

<!-- Uploader -->
<div id="files-upload-multi" class="k-upload--boxed-top"></div>


<div class="k-dynamic-content-holder">
    <?= helper('uploader.scripts') ?>

    <script>
        window.addEvent('domready', function() {
            var timeout = null,
                createUploader = function() {
                    if (Files.app) {
                        Files.createUploader({
                            multi_selection: <?= json_encode((!isset($multi_selection) || $multi_selection !== false) ? true : false) ?>
                        });

                        if (timeout) {
                            clearTimeout(timeout);
                        }
                    } else {
                        timeout = setTimeout(createUploader, 100);
                    }
                };

            createUploader();

        });
    </script>
</div>
