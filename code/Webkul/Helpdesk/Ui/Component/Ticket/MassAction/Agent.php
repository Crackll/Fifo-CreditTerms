<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Ui\Component\Ticket\MassAction;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 */
class Agent implements JsonSerializable
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $_data;

    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Base URL for assign product to warehouse action
     *
     * @var string
     */
    protected $_urlPath;

    /**
     * Param name for assign product to warehouse action
     *
     * @var string
     */
    protected $_paramName;

    /**
     * Additional params for assign product to warehouse action
     *
     * @var array
     */
    protected $_additionalData = [];

    /**
     * Constructor
     *
     * @param Warehouselist $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Webkul\Helpdesk\Model\Agent $agent,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->_agent = $agent;
        $this->_data = $data;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->_options === null) {
            $options = $this->_agent->toOptionArray();
            $this->prepareData();
            foreach ($options as $optCode) {
                if ($optCode['value']) {
                    $this->_options[$optCode['value']] = [
                        'type' => 'tickets_agent_' . $optCode['value'],
                        'label' => $optCode['label'],
                    ];

                    if ($this->_urlPath && $this->_paramName) {
                        $url = $this->_urlBuilder->getUrl(
                            $this->_urlPath,
                            [$this->_paramName => $optCode['value']]
                        );
                        $this->_options[$optCode['value']]['url'] = $url;
                    }

                    $this->_options[$optCode['value']] = array_merge_recursive(
                        $this->_options[$optCode['value']],
                        $this->_additionalData
                    );
                }
            }
            if ($this->_options !== null) {
                $this->_options = array_values($this->_options);
            }
        }

        return $this->_options;
    }

    /**
     * Prepare addition data for ticket status
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->_data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->_urlPath = $value;
                    break;
                case 'paramName':
                    $this->_paramName = $value;
                    break;
                default:
                    $this->_additionalData[$key] = $value;
                    break;
            }
        }
    }
}
