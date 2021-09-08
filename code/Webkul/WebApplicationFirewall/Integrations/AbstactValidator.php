<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
namespace Webkul\WebApplicationFirewall\Integrations;

use Webkul\WebApplicationFirewall\Integrations\AbuseIPDBInterface;
use Webkul\WebApplicationFirewall\Integrations\MailBoxLayerInterface;
use Webkul\WebApplicationFirewall\Api\Data\VulnerabilityInterfaceFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * abstract validator model
 */
abstract class AbstactValidator extends \Magento\Framework\DataObject
{
    /**
     * @var \Webkul\WebApplicationFirewall\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\WebApplicationFirewall\Helper\Integration
     */
    protected $integrationHelper;

    /**
     * @var string
     */
    protected $validationType = null;

    /**
     * @var AbuseIPDBInterface
     */
    protected $abuseIPDB;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param \Webkul\WebApplicationFirewall\Helper\Data $helper
     * @param \Webkul\WebApplicationFirewall\Helper\Integration $integrationHelper
     * @param AbuseIPDBInterface $abuseIPDB
     */
    public function __construct(
        \Webkul\WebApplicationFirewall\Helper\Data $helper,
        \Webkul\WebApplicationFirewall\Helper\Integration $integrationHelper,
        ResourceConnection $resource,
        AbuseIPDBInterface $abuseIPDB,
        MailBoxLayerInterface $mailBoxLayer,
        VulnerabilityInterfaceFactory $vulnerabilityFactory
    ) {
        $this->helper = $helper;
        $this->integrationHelper = $integrationHelper;
        $this->resource = $resource;
        $this->abuseIPDB = $abuseIPDB;
        $this->mailBoxLayer = $mailBoxLayer;
        $this->vulnerabilityFactory = $vulnerabilityFactory;
    }

    abstract public function validate();

    /**
     * Set integration validator type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->setData('type', $type);
        return $this;
    }

    /**
     * Get integration validator type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData('type');
    }

    /**
     * Check if value already validated
     *
     * @param string $value
     * @return bool
     */
    protected function _saveValidatedValue($value)
    {
        if ($value) {
            $savedValues = $this->getValidatedValues();
            $savedValues = explode(',', $savedValues);
            $savedValues[] = $value;
            $newValue = implode(',', array_unique(array_filter($savedValues)));
            $this->saveNewValue($newValue);
        }
    }

    /**
     * Save validated value to stop check again and again.
     *
     * @param string $value
     * @return void
     */
    private function saveNewValue($value)
    {
        $tableName = $this->resource->getTableName('webkul_waf_integration');
        $connection = $this->resource->getConnection();
        $data = [
            'code' => $this->getType(),
            'validated_value' => $value
        ];
        $connection->insertOnDuplicate($tableName, $data, []);
    }

    /**
     * Check if value already validated
     *
     * @param string $value
     * @return bool
     */
    protected function _checkValueAlreadyValidated($value)
    {
        $savedValues = $this->getValidatedValues();
        if ($savedValues) {
            $savedValues = explode(',', $savedValues);
            return in_array($value, $savedValues);
        }
        return false;
    }

    /**
     * Get validated values of integration
     *
     * @return bool|string
     */
    private function getValidatedValues()
    {
        $tableName = $this->resource->getTableName('webkul_waf_integration');
        $connection = $this->resource->getConnection();
        $bind = [
            'code' => $this->getType()
        ];
        $select = $connection->select()
            ->from($tableName, 'validated_value')
            ->where('code = :code');

        return $connection->fetchOne($select, $bind);
    }
}
