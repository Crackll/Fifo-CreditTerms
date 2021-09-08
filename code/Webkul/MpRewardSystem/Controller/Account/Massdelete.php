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
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Url;
use Webkul\MpRewardSystem\Model\RewardcartFactory;

class Massdelete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $customerUrl;
    /**
     * @var \Webkul\MpRewardSystem\Model\RewardcartFactory
     */
    protected $rewardcartFactory;
    /**
     * @param Context             $context
     * @param Session             $customerSession
     * @param FormKeyValidator    $formKeyValidator
     * @param PageFactory         $resultPageFactory
     * @param RewardcartFactory   $rewardcartFactory
     * @param PageFactory         $resultPageFactory
     *
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        Url $customerUrl,
        RewardcartFactory $rewardcartFactory,
        PageFactory $resultPageFactory
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerUrl = $customerUrl;
        $this->rewardcartFactory = $rewardcartFactory;
        parent::__construct($context);
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
        $loginUrl = $this->customerUrl->getLoginUrl();

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
        try {
            if ($this->getRequest()->isPost()) {
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $resultRedirect->setPath(
                        '*/*/cartrecord',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $ids = $this->getRequest()->getParam('cartrule_mass_delete');
                $sellerId = $this->_getSession()->getId();
                $rewardcartCollection = $this->rewardcartFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('seller_id', ['eq'=>$sellerId])
                        ->addFieldToFilter('entity_id', ['in'=>$ids]);
                foreach ($rewardcartCollection as $rewardcart) {
                    $rewardcart->delete();
                }
                $this->messageManager->addSuccess(
                    __('Reward Cart rules are successfully deleted from your account.')
                );
                return $resultRedirect
                    ->setPath(
                        'mprewardsystem/account/cartrecord',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect
                ->setPath(
                    'mprewardsystem/account/cartrecord',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
}
