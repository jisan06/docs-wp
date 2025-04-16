/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

kQuery(function($) {
    var options = {
            history: false,
            shareEl: true,
            shareButtons: [
                {id:'download', label: Koowa.translate('Download'), url:'{{raw_image_url}}', download:true}
            ],
            closeOnScroll: false,
            showAnimationDuration: 0,
            hideAnimationDuration: 0
        },
        pswpElement = document.querySelectorAll('.pswp')[0],
        openGallery = function(items, index) {
            options.index = index;

            var instance = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
            instance.options.getImageURLForShare = function() {
                return instance.currItem.download_link || instance.currItem.src;
            };

            // Get data just in time for faster startup
            instance.listen('gettingData', function(index, item) {
                if (!item.src && !item.html) {
                    var element = item.el;

                    item.track = {
                        id: element.data('id'),
                        title: element.data('title')
                    };

                    if (element.hasClass('koowa_media__item__link--html')) {
                        item.html ='<iframe height="100%" width="100%" src="'+element.attr('href')+'"></iframe>';
                        item.download_link = element.attr('href');
                    } else {
                        item.src = element.attr('href');
                        item.w   = element.data('width') ? parseInt(element.data('width'), 10) : 0;
                        item.h   = element.data('height') ? parseInt(element.data('height'), 10) : 0;
                    }

                    if (element.find('.koowa_header__item')) {
                        item.title = $.trim(element.find('.koowa_header__item--title_container').text());
                    }

                    // Mark preloaded items
                    item.src += '&preload=true';
                }
            });

            instance.listen('afterChange', function(index, item) {
                var item = instance.currItem;

                // Send request to record hit
                if (item.src) {
                    const url = removeUrlParam(item.src, 'preload');   
                    $.get(url);
                }
            });

            instance.listen('imageLoadComplete', function(index, item) {
                if (item.src) {
                    $(document).trigger('photoswipeImageView', [item]);
                }
            });

            instance.listen('destroy', function() {
                $('#wpadminbar').show()
            });

            instance.init();
        },
        getGalleryItems = function(gallery) {
            var items = [];

            $(gallery).find('.k-js-gallery-item').each(function(i, element) {
                element = $(element);
                element.data('index', i);

                items.push({
                    el: element // save link to element for getThumbBoundsFn
                });
            });

            return items;
        };

        $('a.k-js-gallery-item').click(function( event ) {

            event.preventDefault();

            if ($(this).length) {

                var elements = getGalleryItems($(this).parents('.koowa_media--gallery'));

                if (elements)
                {
                    $('#wpadminbar').hide();
                    openGallery(elements, $(this).data('index'));
                }
            }
        });

    // Function to remove URL query parameter
    const removeUrlParam = function(url, parameter) {
        var url_parts = url.split('?');

        if (url_parts.length >= 2) {
            var prefix = encodeURIComponent(parameter) + '=';
            var parts  = url_parts[1].split(/[&;]/g);
    
            for (var i = parts.length; i-- > 0;) {
                if (parts[i].lastIndexOf(prefix, 0) !== -1) {
                    parts.splice(i, 1);
                }
            }
    
            url = url_parts[0] + '?' + parts.join('&');
            return url;
        } else {
            return url;
        }
    }
});
