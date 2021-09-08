  require(
        [
            'Magento_Ui/js/lib/validation/validator',
            'jquery',
            'mage/translate'
    ], function(validator, $){

            validator.addRule(
                'custom-validation',
                function (value) {
                    //return true or false based on your logic
                    if(value=="")
                        return false;
                    else
                        return true;

                }
                ,$.mage.__('This is a required field.')
            );
});
