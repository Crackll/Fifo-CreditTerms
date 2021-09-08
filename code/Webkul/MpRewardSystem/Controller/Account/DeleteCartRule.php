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

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Webkul\MpRewardSystem\Model\RewardcartFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\App\RequestInterface;

class DeleteCartRule extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var Webkul\MpRewardSystem\Model\RewardcartFactory
     */
    protected $rewardcartFactory;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @param Context           $context
     * @param Session           $customerSession
     * @param RewardcartFactory $rewardcartFactory
     * @param Url               $customerUrl
     */

    public function __construct(
        Context $context,
        Session $customerSession,
        RewardcartFactory $rewardcartFactory,
        Url $customerUrl
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->rewardcartFactory = $rewardcartFactory;
        $this->customerUrl = $customerUrl;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $urlModel = $this->customerUrl;
        $loginUrl = $urlModel->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default Shipping set rate
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $fields = $this->getRequest()->getParams();
            if (!empty($fields)) {
                $cartRuleModel = $this->rewardcartFactory->create()
                    ->load($fields['id']);
                if (!empty($cartRuleModel)) {
                    $cartRuleModel->delete();
                    $this->messageManager->addSuccess(__('Reward Cart Rule is successfully Deleted!'));
                    return $resultRedirect->setPath('mprewardsystem/account/cartrecord');
                } else {
                    $this->messageManager->addError(__('No record Found!'));
                    return $resultRedirect->setPath('mprewardsystem/account/cartrecord');
                }
            } else {
                $this->messageManager->addSuccess(__('Please try again!'));
                return $resultRedirect->setPath('mprewardsystem/account/cartrecord');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('mprewardsystem/account/cartrecord');
        }
    }
}
