<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\DataObjectHelper;

class SavePrice extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;
    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var \Webkul\MpRewardSystem\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;
    /**
     *
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param FormKeyValidator $formKeyValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Webkul\MpRewardSystem\Helper\Data $helper
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     *
     *
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        FormKeyValidator $formKeyValidator,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Webkul\MpRewardSystem\Helper\Data $helper,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerMapper = $customerMapper;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->helper = $helper;
        parent::__construct($context);
        $this->url=$url;
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/index',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
            }
            $customerData = $this->getRequest()->getParams();
            $customerId=$this->_getSession()->getCustomerId();
            $savedCustomerData = $this->customerRepository->getById($customerId);

            $baseCurrencyCode = $this->helper->getBaseCurrencyCode();
            $currentCurrencyCode = $this->helper->getCurrentCurrencyCode();
            $allowedCurrencies = $this->helper->getAllowedCurrencies();

            $rate = $this->helper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            if (is_array($rate) && !empty($rate)) {
                $customerData['rewardprice'] = $customerData['rewardprice'] / $rate[$currentCurrencyCode];
            }

            $customer = $this->customerDataFactory->create();
            $customerData = array_merge(
                $this->customerMapper->toFlatArray($savedCustomerData),
                $customerData
            );
            $customerData['id'] = $customerId;
            $this->dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $this->customerRepository->save($customer);
            $this->messageManager->addSuccess(__('Settings has been successfully saved'));
            return $this->resultRedirectFactory->create()
                ->setPath(
                    'mprewardsystem/account/index',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
}
