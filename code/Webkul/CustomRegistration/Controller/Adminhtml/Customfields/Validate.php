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
namespace Webkul\CustomRegistration\Controller\Adminhtml\Customfields;

use Magento\Framework\DataObject;

class Validate extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
     /**
      * @var string
      */
    protected $_customerEntityTypeId;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }
    /**
     * Dispatch request.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_customerEntityTypeId = $this->_objectManager->create(
            \Magento\Eav\Model\Entity::class
        )->setType(
            \Magento\Customer\Model\Customer::ENTITY
        )->getTypeId();

        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $frontendLabel = $this->getRequest()->getParam('frontend_label');
        
        $attributeId = $this->getRequest()->getParam('attribute_id');

        $attribute = $this->_objectManager->create(
            \Magento\Customer\Model\Attribute::class
        )->loadByCode(
            $this->_customerEntityTypeId,
            $attributeCode
        );

        if ($attribute->getId() && !$attributeId) {
            if ($attributeCode !== '') {
                $response->setMessage(
                    __('An attribute with this code already exists.')
                );
            } else {
                $response->setMessage(
                    __('An attribute with the same code (%1) already exists.', $attributeCode)
                );
            }
            $response->setError(true);
        }
        if ($this->getRequest()->has('dependable_attribute_code')) {
            $attributeCode = $this->getRequest()->getParam('dependable_attribute_code');
            $attribute = $this->_objectManager->create(
                \Magento\Customer\Model\Attribute::class
            )->loadByCode(
                $this->_customerEntityTypeId,
                $attributeCode
            );

            if ($attribute->getId() && !$attributeId) {
                if ($this->getRequest()->getParam('dependable_attribute_code') !== '') {
                    $response->setMessage(
                        __('An dependable attribute with this code already exists.')
                    );
                } else {
                    $response->setMessage(
                        __('An dependable attribute with the same code (%1) already exists.', $attributeCode)
                    );
                }
                $response->setError(true);
            }
        }
        if ($this->getRequest()->has('option')) {
            $options = $this->getRequest()->getParam('option');
            $valueOptions = $options['value'] ?? [];
            $this->checkEmptyOption($response, $valueOptions);
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Check that admin does not try to create option with empty admin scope option.
     *
     * @param DataObject $response
     * @param array $optionsForCheck
     * @return void
     */
    private function checkEmptyOption(DataObject $response, array $optionsForCheck = null)
    {
        foreach ($optionsForCheck as $optionValues) {
            if (isset($optionValues[0]) && trim((string)$optionValues[0]) == '') {
                $response->setMessage(
                    __("The value of Admin scope can't be empty.")
                );
                $response->setError(true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_CustomRegistration::index');
    }
}
