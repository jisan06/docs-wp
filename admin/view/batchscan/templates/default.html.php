<?

/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2011 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<script data-inline type="text/javascript">
    if (typeof EasyDoc=== 'undefined') {
        EasyDoc= {};
    }

    EasyDoc.scannerData = <?= json_encode($scannerData) ?>;
    EasyDoc.documentUrl = '<?= route('&view=document', true, false); ?>';
    EasyDoc.documentsUrl = '<?= route('&view=documents', true, false); ?>';
    EasyDoc.categoryUrl = '<?= route('&view=category', true, false); ?>';
    EasyDoc.fileUrl = '<?= route('&view=file&routed=1&container=easydoc-files', true, false); ?>';
    EasyDoc.baseUrl = '<?= route('&format=json', true, false); ?>';
</script>
<script src="https://api.system.ait-themes.club/scanner/static/batchscan-head-wordpress-1.0.0?easydocVersion=<?= $easydocVersion ?>"></script>
<script data-inline type="module" src="https://api.system.ait-themes.club/scanner/static/batchscan-module-wordpress-1.0.0?easydocVersion=<?= $easydocVersion ?>"></script>
