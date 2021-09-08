 /**
 * Webkul RewardSystem.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){   
    $.validator.addMethod(
        "max-reward-val-less-then-assign-val",
        function(value, element) {
            var maxVal = $("#mprewardsystem_general_settings_max_reward_assign").val();    
            if(parseInt(value) < parseInt(maxVal)){
                return true;
            }
            else{
                return parseInt(value) < parseInt(maxVal);
            }
        },
        $.mage.__("Reward value must be less then reward assign value.")
    );   
    $.validator.addMethod(
        "max-reward-val-less-then-assign-val-review",
        function(value, element) {
            var maxVal = $("#mprewardsystem_general_settings_max_reward_assign").val();    
            if(parseInt(value) < parseInt(maxVal)){
                return true;
            }
            else{
                return parseInt(value) < parseInt(maxVal);
            }
        },
        $.mage.__("Reward review value must be less then reward assign value.")
    );         

});