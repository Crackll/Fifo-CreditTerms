<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 */
class CreditStatus implements JsonSerializable
{
    /**
     * @var array
     */
    protected $options;
    
    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;
    
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;
    
    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;
    
    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param array        $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            $options = $this->getOptionArray();
            $this->prepareData();
            foreach ($options as $option) {
                $this->options[$option['value']] = [
                    'type' => 'creditrule_status' . $option['value'],
                    'label' => $option['label'],
                ];
                $this->options[$option['value']]['url'] = $this->urlBuilder->getUrl(
                    $this->urlPath,
                    [$this->paramName => $option['value']]
                );
                $this->options[$option['value']] = array_merge_recursive(
                    $this->options[$option['value']],
                    $this->additionalData
                );
            }
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [
            [
                'value' => \Webkul\MpWalletSystem\Model\Walletcreditrules::WALLET_CREDIT_RULE_STATUS_ENABLE,
                'label' => 'Enabled'
            ], [
                'value' => \Webkul\MpWalletSystem\Model\Walletcreditrules::WALLET_CREDIT_RULE_STATUS_DISABLE,
                'label' => 'Disabled'
            ]
        ];
        return $options;
    }
}
