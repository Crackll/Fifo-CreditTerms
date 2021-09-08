<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RegionUpload\Block\Adminhtml;

class Form extends \Magento\Framework\View\Element\Template
{

    /**
     * Undocumented function
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Directory\Model\Country $country
     * @param \Magento\Directory\Model\Config\Source\Country $countries
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Webkul\RegionUpload\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\Country $country,
        \Magento\Directory\Model\Config\Source\Country $countries,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Webkul\RegionUpload\Helper\Data $helper,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_registry = $registry;
        $this->_country  = $country;
        $this->countries = $countries;
        $this->_regionFactory = $regionFactory;
        $this->formKey = $formKey;
        $this->helper = $helper;
        $this->_localeLists = $localeLists;
        parent::__construct($context, $data);
    }

    /**
     * getCountryList function to get country list
     *
     * @return array
     */
    public function getCountryList()
    {
        $countryCode = $this->getCountryWithRegion();
        $countries = $this->countries->toOptionArray(false);

        $do = [];
        $countryArr = [];
        foreach ($countries as $country) {
            if (!in_array($country['value'], $countryCode)) {
                array_push($countryArr, $country);
            } else {
                array_push($do, $country);
            }
        }
        
        return $countryArr;
    }

    /**
     * Get Country code not having region
     *
     * @return array
     */
    public function getCountryWithRegion()
    {
        $regionColl = $this->_regionFactory->create()->getCollection()
                                            ->addFieldtoFilter('is_manual', ['neq'=> 1])
                                            ->addFieldToSelect('country_id');
        $regionColl->getSelect()->group('country_id');
        $region = [];
        foreach ($regionColl as $regionCollection) {
            array_push($region, $regionCollection->getCountryId());
        }
        return $region;
    }

    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

    /**
     * Get Sampe Csv Url
     *
     * @return void
     */
    public function getSampleCsv()
    {
        return $this->helper->getSampleCsv();
    }

    public function getLocale() {
        return $this->_localeLists->getOptionLocales();
    }
}
