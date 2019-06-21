define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'jquery/file-uploader',
    'mage/translate'
], function ($, mageTemplate) {
    'use strict';

    $.widget('awgc.imageUploader', {
        _create: function () {
            var $container = this.element,
                imageTmpl = mageTemplate(this.options.imageTemplate),
                imageValue = this.options.imageValue,
                imageLoaded = this.options.imageLoaded,
                $dropPlaceholder = this.element.find('.image-placeholder'),
                maximumImageCount = 1;

            var findElement = function (data) {
                return $container.find('.image:not(.image-placeholder)').filter(function () {
                    return $(this).data('image').file === data.file;
                }).first();
            };
            var updateVisibility = function () {
                var elementsList = $container.find('.image:not(.removed-item)');
                elementsList.each(function (index) {
                    $(this)[index < maximumImageCount ? 'show' : 'hide']();
                });
                $dropPlaceholder[elementsList.length > maximumImageCount ? 'hide' : 'show']();
            };

            $container.on('addItem', function (event, data) {
                var tmpl = imageTmpl({
                    data: data
                });

                $(tmpl).data('image', data).insertBefore($dropPlaceholder);
                imageValue.val(data.file);
                updateVisibility();
            });

            $container.on('removeItem', function (event, image) {
                findElement(image).addClass('removed-item').hide();
                imageValue.val('');
                updateVisibility();
            });

            $container.on('click', '[data-role=delete-button]', function (event) {
                event.preventDefault();
                $container.trigger('removeItem', $(event.target).closest('.image').data('image'));
            });

            this.element.find('input[type="file"]').fileupload({
                dataType: 'json',
                dropZone: $dropPlaceholder.closest('[data-attribute-code]'),
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: this.element.data('maxFileSize'),
                done: function (event, data) {
                    $dropPlaceholder.find('.progress-bar').text('').removeClass('in-progress');
                    if (!data.result) {
                        return;
                    }
                    if (!data.result.error) {
                        $container.trigger('addItem', data.result);
                    } else {
                        alert($.mage.__('We don\'t recognize or support this file extension type.'));
                    }
                },
                add: function (event, data) {
                    $(this).fileupload('process', data).done(function () {
                        data.submit();
                    });
                },
                progress: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $dropPlaceholder.find('.progress-bar').addClass('in-progress').text(progress + '%');
                },
                start: function (event) {
                    var uploaderContainer = $(event.target).closest('.image-placeholder');

                    uploaderContainer.addClass('loading');
                },
                stop: function (event) {
                    var uploaderContainer = $(event.target).closest('.image-placeholder');

                    uploaderContainer.removeClass('loading');
                }
            });

            if (imageLoaded) {
                var data = {'url': imageLoaded};
                var tmpl = imageTmpl({
                    data: data
                });

                $(tmpl).data('image', data).insertBefore($dropPlaceholder);
                updateVisibility();
            }
        }
    });

    return $.awgc.imageUploader;
});
