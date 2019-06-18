define([], function(){
    'use strict';
    return function(targetModule){
        targetModule.prototype.initialize = function(formId, firstFieldFocus){
            this.form = $(formId);
            if (!this.form) {
                return;
            }
            this.cache = $A();
            this.currLoader = false;
            this.currDataIndex = false;
            Validation.addAllThese([
                ['validate-confirm-email', 'Please make sure your emails match.', function(v) {
                    var conf = $$('.validate-confirm-email')[0];
                    var pass = $$('.validate-email')[0];
                    if ($$('.validate-admin-email').size()) {
                        pass = $$('.validate-admin-email')[0];
                    }
                    return (pass.value == conf.value);
                }]
            ]);
            this.validator = new Validation(this.form);
            this.elementFocus = this.elementOnFocus.bindAsEventListener(this);
            this.elementBlur = this.elementOnBlur.bindAsEventListener(this);
            this.childLoader = this.onChangeChildLoad.bindAsEventListener(this);
            this.highlightClass = 'highlight';
            this.extraChildParams = '';
            this.firstFieldFocus = firstFieldFocus || false;
            this.bindElements();
            if (this.firstFieldFocus) {
                try {
                    Form.Element.focus(Form.findFirstElement(this.form))
                }
                catch (e) {
                }
            }
        }
        targetModule.crazyPropertyAddedHere = 'yes';
        return targetModule;
    };
});

