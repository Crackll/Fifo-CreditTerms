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

namespace Webkul\MpWalletSystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpWalletSystem\Model\WalletUpdateData;
use Webkul\MpWalletSystem\Model\Wallettransaction;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Webkul MpWalletSystem Controller
 */
class Payeedelete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Webkul\MpWalletSystem\Helper\Mail
     */
    protected $walletHelper;
    
    /**
     * @var Webkul\MpWalletSystem\Model\WalletUpdateData
     */
    protected $walletUpdate;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var $walletPayee
     */
    protected $walletPayee;
    
    /**
     * Initialize depndencies
     *
     * @param Context                                            $context
     * @param PageFactory                                        $resultPageFactory
     * @param \Webkul\MpWalletSystem\Helper\Data                 $walletHelper
     * @param WalletUpdateData                                   $walletUpdate
     * @param \Magento\Customer\Model\CustomerFactory            $customerModel
     * @param StoreManagerInterface                              $storeManager
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param \Webkul\MpWalletSystem\Model\WalletPayeeFactory    $walletPayee
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        WalletUpdateData $walletUpdate,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpWalletSystem\Model\WalletPayeeFactory $walletPayee
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->walletUpdate = $walletUpdate;
        $this->customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->walletPayee = $walletPayee;
        parent::__construct($context);
    }
    
    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->validateParams($params);
        return $this->resultRedirectFactory->create()->setPath(
            'mpwalletsystem/transfer/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return bool
     */
    protected function validateParams($params)
    {
        if (isset($params) && is_array($params) && array_key_exists('id', $params) && $params['id']!='') {
            $this->deletePayee($params);
        } else {
            $this->messageManager->addError(
                __(
                    "There is some error during executing this process, please try again later."
                )
            );
        }
    }

    /**
     * Delete payee
     *
     * @param array $params
     * @return void
     */
    public function deletePayee($params)
    {
        $payeeModel = $this->walletPayee->create()->load($params['id']);
        $payeeModel->delete();
        $this->messageManager->addSuccess(__("Payee is successfully deleted"));
    }
}
