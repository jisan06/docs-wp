/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

(function(wp, editor, blockEditor, components, blocks, global)
{
    document.addEventListener("DOMContentLoaded", function()
    {
        var el = wp.element.createElement;

        var blockName = 'easydoc/attachments';
        var config = FoliokitBlockConfigurations[blockName];

        EasyDoc.Block.setConfig(config);

        var onSelect = function() {};

        window.addEventListener("message", function(event) {
            if (typeof event.data === 'object' && event.data.publisher === 'easy-docs-ait/attachments'
                && event.data.event === 'selected-documents') {

                onSelect(event.data.selected);

                kQuery.magnificPopup.close();
            }
        });

        var preview_attributes = ['id', 'title', 'icon', 'image', 'size', 'kind', 'category_title'];


        config.edit = function(props) {

            var _ = Foliokit.translate;

            onSelect = function(selected) {

                var document_ids = [];
                var preview = [];
                for (var document of selected) {
                    document_ids.push(document.id);

                    var preview_object = {};
                    for (var attr of preview_attributes) {
                        preview_object[attr] = document[attr];
                    }

                    preview.push(preview_object);

                }

                props.setAttributes ( { documents: document_ids, preview: preview } );
            };

            var placeholderConfig = {
                label: config.title,
                icon: config.icon
            };

            var instructions = el('ul', {}, []);

            if (props.attributes.preview && props.attributes.preview.length) {
                var docList = [];

                for (var document of props.attributes.preview) {
                    docList.push(el('li', {}, document.title));
                }

                instructions = el('ul', {}, [docList]);
            }

            return [
                el(
                    blockEditor.InspectorControls,
                    null,
                    el(components.PanelBody, {
                        title: _('Layout'),
                        initialOpen: true
                    }, [
                        EasyDoc.Block.getControl('layout', config.attributes.layout, props),
                    ]),
                    el(components.PanelBody, {
                        title: _('Behavior'),
                        initialOpen: true
                    }, [
                        EasyDoc.Block.getControl('sort_documents', config.attributes.sort_documents, props),
						EasyDoc.Block.getControl('show_document_hits', config.attributes.show_document_hits, props),
                        EasyDoc.Block.getControl('show_document_extension', config.attributes.show_document_extension, props),
                        EasyDoc.Block.getControl('show_document_size', config.attributes.show_document_size, props),
                        EasyDoc.Block.getControl('show_icon', config.attributes.show_icon, props),
						EasyDoc.Block.getControl('show_document_created', config.attributes.show_document_created, props),
                        //EasyDoc.Block.getControl('link_to', config.attributes.link_to, props),
                        EasyDoc.Block.getControl('force_download', config.attributes.force_download, props),
                        EasyDoc.Block.getControl('download_in_blank_page', config.attributes.download_in_blank_page, props)
                    ])
                ),
                el(components.Placeholder, placeholderConfig, [
                    instructions,
                    el('form', {}, [
                        el(components.Button, {
                            isPrimary: true,
                            onClick: function() {
                                kQuery.magnificPopup.open({
                                    items: {
                                        src: '?component=easydoc&view=documents&page=easydoc-documents&layout=attachments&category=' + config.attributes._category.default,
                                        type: 'iframe'
                                    },
                                    mainClass: 'koowa_dialog_modal'
                                });
                            }
                        }, [props.attributes.documents ? _('Edit') : _('Select')])
                    ])
                ])
            ];
        };

        config.save = function()  {
            return null; //save has to exist. This all we need
        };

        config.icon = EasyDoc.Block.icon;

        blocks.registerBlockType(blockName, config);

        global.EasyDoc.Block.setNodeHandler('.attachments_layout_selector', function(node) {
			EasyDoc.Block.addHandler({values: ['list', 'table', 'gallery'], element: node.find('select'), handler: 'selectVisibility'});
        });
    });
})(wp, wp.editor, wp.blockEditor, wp.components, wp.blocks, window);
