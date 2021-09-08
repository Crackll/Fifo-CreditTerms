<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Block;

use Magento\Store\Model\ScopeInterface;

class Condition extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface
     */
    protected $agreementsRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Agreement constructor
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Artera\Privacy\Model\Agreement                  $agreement
     * @param \Artera\Privacy\Model\Page                       $page
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $agreementsRepository,
        array $data = []
    ) {
        $this->agreementsRepository = $agreementsRepository;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'customer_termandcondition/parameter/'.$field;
      
        return preg_replace(
            "/<script>.+?<\/script>/i",
            "",
            $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()->getId()
            )
        );
    }

    /**
     * Selected agreement id getter
     * @return int
     */
    protected function getConditionId()
    {
        return (int)$this->scopeConfig->getValue(
            'privacy/settings/agreement_id',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @return \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface|bool
     */
    public function getConditionData()
    {
        if ($this->getConfigData('termcondition') != '') {
            return $this->getConfigData('termcondition');
        } else {
            return false;
        }
    }

    public function getPrivacyData()
    {
        if ($this->getConfigData('privacy') != '') {
            return $this->getConfigData('privacy');
        } else {
            return false;
        }
    }

    public function getConditionContent()
    {
        $condition = $this->getConditionData();
        $privacy = $this->getPrivacyData();
        $conditionData = '';
        $privacyData = '';
        if ($condition !== false) {
            if ($this->getConfigData('is_html')) {
                $conditionData = $condition;
            } else {
                $conditionData = nl2br($this->escapeHtml($condition));
            }
        }

        if ($privacy !== false) {
            if ($this->getConfigData('is_html')) {
                $privacyData = $privacy;
            } else {
                $privacyData = nl2br($this->escapeHtml($privacy));
            }
        }
        return json_encode(
            [
                'condition' => $conditionData,
                'privacy' => $privacyData,
                'privacyheading' => $this->getConfigData('privacy_heading'),
                'termheading' => $this->getConfigData('term_heading'),
                'animate' => $this->getConfigData('animate'),
                'buttontitle' => $this->getConfigData('button_text')
            ]
        );
    }

    public function getModelHeading()
    {
        return $this->getConfigData('privacy_heading');
    }
}
