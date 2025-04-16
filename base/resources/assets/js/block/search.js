/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

(function(wp, editor, blockEditor, components, blocks, global) {

    document.addEventListener("DOMContentLoaded", function() {
        var el = wp.element.createElement;

        var blockName = 'easydoc/search';
        var config = FoliokitBlockConfigurations[blockName];

        EasyDoc.Block.setConfig(config);

        config.edit = function (props) {

            var _ = Foliokit.translate;

            return [
                el(
                    blockEditor.InspectorControls,
                    null,
                    [

                        el(components.PanelBody, {
                            title: _('Layout'),
                            initialOpen: true
                        }, [
                            EasyDoc.Block.getControl('results_layout', config.attributes.results_layout, props),
                        ]),
                        el(components.PanelBody, {
                            title: _('Document Options'),
                            initialOpen: false
                        }, [
                            EasyDoc.Block.getControl('sort_documents', config.attributes.sort_documents, props),
                            EasyDoc.Block.getControl('documents_per_page', config.attributes.documents_per_page, props),
                            EasyDoc.Block.getControl('show_document_sort_limit', config.attributes.show_document_sort_limit, props),
                            //EasyDoc.Block.getControl('show_document_tags', config.attributes.show_document_tags, props), TODO remove comment when document view/preview is available
                            EasyDoc.Block.getControl('show_document_created', config.attributes.show_document_created, props),
                            EasyDoc.Block.getControl('show_document_created_by', config.attributes.show_document_created_by, props),
                            EasyDoc.Block.getControl('show_document_modified', config.attributes.show_document_modified, props),
                            EasyDoc.Block.getControl('show_document_filename', config.attributes.show_document_filename, props),
                            EasyDoc.Block.getControl('show_document_size', config.attributes.show_document_size, props),
                            EasyDoc.Block.getControl('show_document_hits', config.attributes.show_document_hits, props),
                            EasyDoc.Block.getControl('show_document_extension', config.attributes.show_document_extension, props),
                            EasyDoc.Block.getControl('track_downloads', config.attributes.track_downloads, props),
                            EasyDoc.Block.getControl('force_download', config.attributes.force_download, props),
                            //EasyDoc.Block.getControl('allow_multi_download', config.attributes.allow_multi_download, props),
                            EasyDoc.Block.getControl('download_in_blank_page', config.attributes.download_in_blank_page, props),
                        ]),
                    ]
                ),
                el(components.Placeholder, {
                    label: config.title,
                    icon: config.icon
                }, [])
            ];
        };

        config.save = function () {
            return null; //save has to exist. This all we need
        };

        config.icon = EasyDoc.Block.icon;

        blocks.registerBlockType(blockName, config);

        global.EasyDoc.Block.setNodeHandler('.search_layout_selector', function(node) {
			EasyDoc.Block.addHandler({values: ['list', 'table', 'gallery'], element: node.find('select'), handler: 'selectVisibility'});
        });
    });
})(wp, wp.editor, wp.blockEditor, wp.components, wp.blocks, window);
