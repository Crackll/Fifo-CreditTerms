/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

 define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/checkout-data',
    'Webkul_RegionUpload/js/view/shippingMap-js',
    'Webkul_RegionUpload/js/model/map-config-provider',
    'Magento_Checkout/js/model/payment-service'
  ], function ($, Component, ko, $t, alert, modal, magecheck, shippingMap, mapData, paymentData) {
    'use strict';
    var i = 1;
    var markerbilling = '';
    var selectedMethod = '';
    var timer = '';
    var countryId = '';
    var countryName = '';
    var postalCode = '';
    var stateName = '';
    var addressData = '';
    var latitude = -25.33333;
    var longitude = 131.044;
    var mapDataValue = mapData.getMapData();
    var paymentMethodList = '';
    return Component.extend({
        initCustomEvents: function () {
          paymentMethodList = paymentData.getAvailablePaymentMethods();
            var self = this;
            $(".billing-address-same-as-shipping-block").click(function(event) {
                if ($(this).find("input[type = checkbox]").is(":checked") == false) {
                selectedMethod =  magecheck.getSelectedPaymentMethod();
                if (selectedMethod == null) {
                  selectedMethod =  paymentMethodList[0]['method'];
                }
                if (window.checkoutConfig.displayBillingOnPaymentMethod == false ) {
                    selectedMethod = "shared";
                }
                var longiDivName =  "billingAddress"+selectedMethod+".custom_attributes.longitude";
                var linkurl =window.location.href;
                var res = linkurl.match(/payment/g);
                if (res[0]== 'payment') {
                            $("div[name = 'billingAddress"+selectedMethod+".telephone']").append($(".mapContainerBilling"));
                            $("div[name = 'billingAddress"+selectedMethod+".telephone']").css("margin-bottom","130px");
                            $(".mapContainerBilling").show();
                      if (mapDataValue['status'] != '0') {    
                          if ($(".mapContainerBilling").length) {
                        if (!$(".billing-address-form .fieldset.address .choice.field").length) {
                            $("div[name = 'billingAddresscashondelivery.telephone']").css({'margin-bottom':'200px'});
                        }
                      }
                    }
                 }
             }
            });
            
            $('.opc-progress-bar-item').click(function(events) {
              var linkurl =window.location.href;
                  var res = linkurl.match(/shipping/g);
                  if (res[0]== 'shipping') {
                    $(".mapContainerBilling").hide();
                    if (i) {
                      shippingMap().onElementRender(); 
                      i = 0;
                    } 
                  }
          });
  
          timer =  setTimeout(function () {
            if ($(document).find(".billing-address-details .action.action-edit-address").length) {
              $(".billing-address-details .action.action-edit-address").click(function() {
                selectedMethod =  magecheck.getSelectedPaymentMethod();
                if (selectedMethod == null) {
                  selectedMethod =  paymentMethodList[0]['method'];
                }
                var longiDivName =  "billingAddress"+selectedMethod+".custom_attributes.longitude";
                var linkurl =window.location.href;
                var res = linkurl.match(/payment/g);
                if (res[0]== 'payment') {
                            $("div[name = '"+longiDivName+"']").append($(".mapContainerBilling"));
                            $(".mapContainerBilling").show();
                        if (!$(".billing-address-form .fieldset.address .choice.field").length) {
                            $("div[name = '"+longiDivName+"']").css({'margin-bottom':'200px'});
                        }
                    };
              });
              clearTimeout(timer);
            }
        }, 500);
        },
        _create: function() {
        },
        afterElementRenderForCheckout: function () {
            var self = this;
        if (mapDataValue['status'] != '0') {
        if (mapDataValue['api_key'] != null) {
                self.initCustomEvents();
                selectedMethod =  magecheck.getSelectedPaymentMethod();
                if (selectedMethod == null) {
                selectedMethod =  paymentMethodList[0]['method'];
                }
                var myLatLng = {lat:parseFloat(latitude), lng:parseFloat(longitude)};     
                var mapbilling = new google.maps.Map(document.getElementById('mapbilling'), {
                    center: myLatLng,
                    zoom: 8
                });
                markerbilling = new google.maps.Marker({
                    position: myLatLng,
                    map: mapbilling,
                    title: 'PinDrop',
                    draggable: true
                });
                    google.maps.event.addListener(markerbilling, 'dragend', function (event) {   
                        var latit = this.getPosition().lat();
                        var longi = this.getPosition().lng();
                        latitude = latit;
                        longitude = longi;
                        var latLng = {lat:latit,lng:longi};
                        geoCoderLocationGate(latLng);                             
                    });
                    function geoCoderLocationGate(latLng){
                        var geocoder = new google.maps.Geocoder();
                        var streetAddress = '';
                        geocoder.geocode({
                        'latLng':latLng
                    }, function (results, status) {
                        if (status ==
                            google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                var addrComp = results[0].address_components;
                                    for (var i=addrComp.length-1;i>=0;i--) {
                                    if (addrComp[i].types[0] =="country")
                                    {
                                        var countryDivName = "billingAddress"+selectedMethod+".country_id";
                                        var country = addrComp[i].short_name;
                                            $("div[name = '"+countryDivName+"'] select[name='country_id'] option[value='"+country+"']").attr("selected",true);
                                            $("div[name = '"+countryDivName+"'] select[name='country_id']").trigger('change');
                                    }
                                    else if (addrComp[i].types[0] == "administrative_area_level_1")
                                    {
                                        var state = addrComp[i].long_name;
                                        var stateDivName = "billingAddress"+selectedMethod+".region_id"
                                        if($("div[name = '"+stateDivName+"'] select[name = 'region_id']").is(':visible')){
                                            $('div[name = "'+stateDivName+'"] select[name = "region_id"] option:contains("'+state+'")').attr("selected",true);
                                            $('input[name = region]').attr("value",'');
                                            $('div[name = "'+stateDivName+'"] select[name = "region_id"]').trigger('change');
                                        }
                                        else
                                        {
                                        var stateDivName = "billingAddress"+selectedMethod+".region";
                                            $('div[name = "'+stateDivName+'"] input[name = region]').val(state);
                                            $('div[name = "'+stateDivName+'"] input[name = region]').trigger('keyup');  
                                        }
                                    }
                                    else if (addrComp[i].types[0] == "administrative_area_level_2")
                                    {
                                        var city = addrComp[i].long_name;
                                        var cityDivName = "billingAddress"+selectedMethod+".city";
                                        $('div[name = "'+cityDivName+'"] input[name="city"]').val(city);
                                        $('div[name = "'+cityDivName+'"] input[name="city"]').trigger('keyup');
                                    }
                                    else if (addrComp[i].types[0] == "postal_code")
                                    {
                                        var postal = addrComp[i].long_name;
                                        var postalDivName = "billingAddress"+selectedMethod+".postcode";
                                        $('div[name = "'+postalDivName+'"] input[name="postcode"]').val(postal);
                                        $('div[name = "'+postalDivName+'"] input[name="postcode"]').trigger('keyup');
                                        }  else if (addrComp[i].types[0] == 'street_number') {
                                            streetAddress = addrComp[i].long_name +", "+ streetAddress;
                                        } else if (addrComp[i].types[0] == 'route') {
                                            streetAddress = addrComp[i].long_name +", "+ streetAddress;
                                        } else if (addrComp[i].types[0] == 'neighborhood') {
                                            streetAddress = addrComp[i].long_name +", "+ streetAddress;
                                        } else if (addrComp[i].types[0] == 'sublocality_level_3') {
                                            streetAddress = addrComp[i].long_name +", "+ streetAddress;
                                        } else if (addrComp[i].types[0] == 'sublocality_level_2') {
                                            streetAddress = addrComp[i].long_name +", "+ streetAddress;
                                        } else if (addrComp[i].types[0] == 'sublocality_level_1') {
                                            streetAddress = addrComp[i].long_name +", "+ streetAddress;
                                        } else if (addrComp[i].types[0] == 'locality') {
                                            streetAddress = addrComp[i].long_name+", "+ streetAddress;
                                        }
                                    }
                                    if (streetAddress) {
                                        streetAddress = streetAddress.trim();
                                        streetAddress = streetAddress.substring(0,streetAddress.length-1);
                                        var streetDivName = "billingAddress"+selectedMethod+".street.0";
                                        $("div[name = '"+streetDivName+"'] input[name = 'street[0]']").val(streetAddress)
                                        $("div[name = '"+streetDivName+"'] input[name = 'street[0]']").trigger('keyup');
                                    }
                            } else {
                                alert({content:$t("No results found.")});
                            }
                        } else {
                            console.log(status);
                            alert({content:$t("Something went wrong.")});
                        }
                    });
            
                    }
                    function geoCoderLocationGatebyCustomAddress(addressData){
                        var geocoder = new google.maps.Geocoder();
                        geocoder.geocode({
                        'address':addressData
                    }, function (results, status) {
                        if (status ==
                            google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                var addrLatitude = results[0].geometry.location.lat();
                                latitude = addrLatitude;
                                var addrLongitude = results[0].geometry.location.lng();
                                longitude = addrLongitude;
                                var latLangByAddress = {lat:addrLatitude, lng:addrLongitude};
                                markerbilling.setPosition(latLangByAddress);
                                mapbilling.setCenter(latLangByAddress);
                                geoCoderLocationGate(latLangByAddress);
                            } else {
                                alert({content:$t("No results found.")});
                            }
                        } else {
                            console.log(status);
                            alert({content:$t("Something went wrong.")});
                        }
                    });
                    }
                    function loadEvents(){
                        $(document).find("div[name ='billingAddress"+selectedMethod+".country_id'] select[name='country_id']").focusout(function(){
                        countryId =  $(document).find("div[name ='billingAddress"+selectedMethod+".country_id'] select[name='country_id']").val();
                        countryName = $(document).find("div[name ='billingAddress"+selectedMethod+".country_id'] select[name='country_id'] option[value='"+countryId+"']").text();
                        
                        if (countryName && postalCode && stateName)
                        {
                            addressData = stateName+" "+postalCode+", "+countryName;
                            getAddressBilling(addressData);
                        }
                        });
                        $(document).find("div[name ='billingAddress"+selectedMethod+".region_id'] select[name = 'region_id']").focusout(function(){
                        stateName =  $(document).find("div[name ='billingAddress"+selectedMethod+".region_id'] select[name='region_id'] option:selected").text();
                        
                        if (countryName && postalCode && stateName)
                        {
                            addressData = stateName+" "+postalCode+", "+countryName;
                            getAddressBilling(addressData);
                        }
                        });
                        $(document).find("div[name ='billingAddress"+selectedMethod+".region'] input[name = 'region']").focusout(function(){
                        stateName =  $(document).find("div[name ='billingAddress"+selectedMethod+".region'] input[name='region']").val();
                        
                        if (countryName && postalCode && stateName)
                        {
                            addressData = stateName+" "+postalCode+", "+countryName;
                            getAddressBilling(addressData);
                        }
                        });
                        $(document).find("div[name ='billingAddress"+selectedMethod+".postcode'] input[name = 'postcode']").focusout(function(){
                        postalCode =  $(document).find("div[name ='billingAddress"+selectedMethod+".postcode'] input[name='postcode']").val();  
                        if (countryName && postalCode && stateName)
                        {
                            addressData = stateName+" "+postalCode+", "+countryName;
                            getAddressBilling(addressData);
                        }
                        });
                    }
                    timer =  setTimeout(function () {
                        if ($(document).find("div[name ='billingAddress"+selectedMethod+".country_id'] select[name='country_id']").length) {
                        loadEvents();
                        clearTimeout(timer);
                        };
                    }, 500);
                    function getAddressBilling(addressData){
                    geoCoderLocationGatebyCustomAddress(addressData);
                    countryId = '';
                    countryName = '';
                    postalCode = '';
                    stateName = '';
                    addressData = '';
                    } 
                }
            }
        }
    });
  });
  