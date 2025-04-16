/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

(function(wp, editor, blockEditor, components, blocks)
{
    document.addEventListener("DOMContentLoaded", function()
    {
        var el = wp.element.createElement;

        var blockName = 'easydoc/submit';
        var config = FoliokitBlockConfigurations[blockName];

        EasyDoc.Block.setConfig(config);

        config.edit = function(props) {

            var _ = Foliokit.translate;

            return [
                el(
                    blockEditor.InspectorControls,
                    null,
                    el(components.PanelBody, {
                        title: _('General'),
                        initialOpen: true
                    }, [
                        EasyDoc.Block.getControl('category_id', config.attributes.category_id, props),
                        EasyDoc.Block.getControl('category_children', config.attributes.category_children, props),
                        EasyDoc.Block.getControl('auto_publish', config.attributes.auto_publish, props),
                        EasyDoc.Block.getControl('show_description', config.attributes.show_description, props),
                    ]),
                    el(components.PanelBody, {
                        title: _('Notifications'),
                        initialOpen: true
                    }, [
                        EasyDoc.Block.getControl('notification_emails', config.attributes.notification_emails, props),
                    ])
                ),
                el(components.Placeholder, {
                    label: config.title,
                    icon: config.icon
                }, [

                ])
            ];
        };

        config.save = function()  {
            return null; //save has to exist. This all we need
        };

        config.icon = EasyDoc.Block.icon;


        blocks.registerBlockType(blockName, config);
    });
})(wp, wp.editor, wp.blockEditor, wp.components, wp.blocks);
