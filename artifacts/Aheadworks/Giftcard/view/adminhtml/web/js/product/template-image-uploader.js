define([
    'Magento_Ui/js/form/element/file-uploader'
], function (Element) {
    'use strict';

    return Element.extend({
        onFileUploaded: function (e, data) {
            var file    = data.result,
                error   = file.error;

            if (error) {
                this.notifyError(error);
            } else {
                this.addFile(file);
                var el = jQuery("input[name='" + this.inputName + "']");
                el.val(file.file);
                el.prev('.file-uploader').children('.image-placeholder').hide();
            }
        },
        removeFile: function (file) {
            this.value.remove(file);
            var el = jQuery("input[name='" + this.inputName + "']");
            el.val('');
            el.prev('.file-uploader').children('.image-placeholder').show();
            return this;
        },
        setInitialValue: function () {
            var value = this.getInitialValue();
            if (value) {
                jQuery.each( this.images, function( i, val ) {
                    if (val.image == value) {
                        value = new Array({
                            file:value,
                            name:val.name,
                            url:val.image_url});
                        var el = jQuery("input[name='" + this.inputName + "']");
                        el.val(value);
                        el.prev('.file-uploader').children('.image-placeholder').hide();
                    }
                });
            }
            value = value.map(this.processFile, this);
            this.initialValue = value.slice();
            this.value(value);
            this.on('value', this.onUpdate.bind(this));
            return this;
        },
        onElementRender: function (fileInput) {
            this.initUploader(fileInput);
        },
        onPlaceholderRender: function (el) {
            jQuery.each(this.initialValue, function(i, val) {
                if (val.file) {
                    el.hide();
                }
            });
        },
        onHiddenValueRender: function (el) {
            jQuery.each(this.initialValue, function(i, val) {
                if (val.file) {
                    el.value = val.file;
                }
            });
        },
    });
});


