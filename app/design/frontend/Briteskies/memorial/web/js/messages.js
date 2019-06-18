// We extend the messages component to increase the visibility length time of the message.
define([
], function(){
    'use strict';

    return function(Messages) {
        return Messages.extend({
            onHiddenChange: function (isHidden) {
                var self = this;
                // Hide message block if needed
                if (isHidden) {
                    setTimeout(function () {
                        $(self.selector).hide('blind', {}, 500)
                    }, 60000);
                }
            }
        });
    }
});
