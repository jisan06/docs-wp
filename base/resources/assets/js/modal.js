/**
 * EasyDocs Modal
 *
 * Behaviors related to ComEasyDocTemplateHelperModal
 *
 * @copyright	Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @requires    Koowa, Koowa.Class
 */

(function($){

    /** @namespace EasyDoc*/
    if (typeof EasyDoc=== 'undefined') { //noinspection JSUndeclaredVariable
        EasyDoc= {};
    }

    /** @namespace EasyDoc.Modal */
    EasyDoc.Modal = {};

    /**
     * @mixin
     * @extends Koowa.Class
     */
    EasyDoc.Modal.Class = Koowa.Class.extend(
        {
            /**
             * @property {object} element - Cached jQuery object of the html input element storing the value
             */
            element: false,

            /**
             * @namespace
             * @property {object}  options                - The default option values.
             * @property {string}  options.id             - The default id of the html input element.
             */
            options: {
                id: false
            },

            initialize: function(options){

                this.setOptions(options);

                this.element = $('#' + this.options.id);

                var self = this;
                EasyDoc.Modal.request_map[options.callback] = self.callback.bind(self);
            }
        }
    );

    /**
     * @class EasyDoc.Modal.Icon
     * @extends EasyDoc.Modal.Class
     */
    EasyDoc.Modal.Icon = EasyDoc.Modal.Class.extend({

        /**
         * @namespace
         * @property {object}  options                   - The default option values.
         * @property {string}  options.id                - The default id of the html input element.
         * @property {string}  options.custom_icon_path  - Custom icon path, icon:// parsed by php to the custom icon folder root url.
         * @property {string}  options.blank_icon_path   - Full url to a blank png for failed select fallbacks.
         */
        options: {
            custom_icon_path: "icon://",
            blank_icon_path: "media://system/images/blank.png"
        },
        initialize: function(options){

            //noinspection JSUnresolvedFunction
            /** Call parent construct */
            this.supr(options);

            var preview = $('#' + this.options.id + '-preview'),
                font_preview = $('#' + this.options.id + '-font-preview'),
                value = '',
                self = this,
                icon_path = this.options.custom_icon_path,
                dropdown = preview.parent(),
                event = function(){
                    var el = $(this),
                        value = el.val();
                    if (value.substr(0, 5) === 'icon:' || !value) {
                        value = (value ? icon_path + '/' + value.substr(5) : self.options.blank_icon_path);

                        preview.attr('src', value);
                        preview.css('display', 'inline');
                        font_preview.css('display', 'none');
                    } else {
                        var classes = font_preview.attr('class').split(' ');

                        $.each(classes, function(i, cls) {
                            if (cls.substr(0, 16) === 'k-icon-document-') {
                                font_preview.removeClass(cls);
                            }
                        });

                        font_preview.addClass('k-icon-document-'+value);

                        preview.css('display', 'none');
                        font_preview.css('display', 'inline-block');
                    }

                    //Breaks on Joomla 3.0 due to no event argument being passed to Dropdown.toggle
                    //dropdown.dropdown('toggle');
                    //Workaround
                    if(dropdown.parent().hasClass('open')) dropdown.trigger('click');
                };

            this.element.closest('ul').find('.k-js-document-icon-selector').click(function(e){
                e.preventDefault();

                $('#'+self.options.id).val($(this).attr('data-value')).trigger('change');
            });

            this.element.on('change', event);
        },
        /**
         * Callback event fired by the iframe handler when a file is selected
         * @param {string} selected
         */
        callback: function(selected){
            this.element.val('icon:'+selected).trigger('change');

            $.magnificPopup.close();
        }
    });

    /**
     * Global request map, used in iframe and JSONP style callbacks
     * @memberOf EasyDoc.Modal
     * @type {object}
     */
    EasyDoc.Modal.request_map = {};

})(kQuery);