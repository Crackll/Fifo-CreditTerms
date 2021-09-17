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
use Magento\Framework\Stdlib\DateTime\DateTime;

class Updatecartrule extends \Magento\Customer\Controller\AbstractAccount
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
     * @var \Webkul\MpRewardSystem\Helper\Data
     */
    protected $helper;
    /**
     * @var \Webkul\MpRewardSystem\Model\RewardcartFactory
     */
    protected $rewardcartFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param Url $customerUrl
     * @param \Webkul\MpRewardSystem\Helper\Data $helper
     * @param RewardcartFactory $rewardcartFactory
     * @param DateTime $date
     * @param PageFactory $resultPageFactory
     *
     *
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        Url $customerUrl,
        \Webkul\MpRewardSystem\Helper\Data $helper,
        RewardcartFactory $rewardcartFactory,
        DateTime $date,
        PageFactory $resultPageFactory
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerUrl = $customerUrl;
        $this->helper = $helper;
        $this->rewardcartFactory = $rewardcartFactory;
        $this->date = $date;
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
        $wholeData = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost()) {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $resultRedirect->setPath(
                    '*/*/cartrecord',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $wholeData['seller_id'] = $this->_getSession()->getId();
            $error = $this->validateData($wholeData);
            if (is_array($error) && isset($error[0])) {
                $this->messageManager->addError(__($error[0]));
                return $resultRedirect->setPath(
                    '*/*/cartrecord',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $model = $this->rewardcartFactory->create();
            $duplicate = $this->checkForAlreadyExists($wholeData);
            if ($duplicate) {
                $this->messageManager->addError(__("Amount range already exist."));
                return $resultRedirect->setPath(
                    '*/*/cartrecord',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $id = $this->getRequest()->getParam('entity_id');
            $model->load($id);
            $model->setData($wholeData);
            try {
                $model->save();
                $this->messageManager->addSuccess(
                    __('Cart Rule successfully updated.')
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the data.')
                );
            }
            return $resultRedirect
                ->setPath(
                    'mprewardsystem/account/cartrecord',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
    /**
     * check all exixting cart rule
     *
     * @param [] $data
     * @return true|false
     */
    public function checkForAlreadyExists($data)
    {
        $rewardCartSet1 = $this->rewardcartFactory->create()->getCollection()
                                  ->addFieldToFilter('amount_from', ['lteq'=>$data['amount_from']])
                                  ->addFieldToFilter('amount_to', ['gteq'=>$data['amount_to']])
                                  ->addFieldToFilter('seller_id', ['eq'=>$data['seller_id']])
                                  ->addFieldToFilter('start_date', ['lteq'=>$data['start_date']])
                                  ->addFieldToFilter('end_date', ['gteq'=>$data['end_date']]);
        $rewardCartSet2 = $this->rewardcartFactory->create()->getCollection()
                                  ->addFieldToFilter('amount_from', ['lteq'=>$data['amount_to']])
                                  ->addFieldToFilter('amount_to', ['gteq'=>$data['amount_from']])
                                  ->addFieldToFilter('seller_id', ['eq'=>$data['seller_id']])
                                  ->addFieldToFilter('start_date', ['lteq'=>$data['start_date']])
                                  ->addFieldToFilter('end_date', ['gteq'=>$data['end_date']]);
        if (isset($data['entity_id'])) {
            $rewardCartSet1->addFieldToFilter("entity_id", ["neq" =>$data['entity_id']]);
            $rewardCartSet2->addFieldToFilter("entity_id", ["neq" =>$data['entity_id']]);
        }
        if ((is_array($rewardCartSet1) && !empty($rewardCartSet1))
        || (is_array($rewardCartSet2) && !empty($rewardCartSet2))) {
            return true;
        }
        return false;
    }
    /**
     * validate cart rule on date
     *
     * @param array $data
     * @return array
     */
    public function validateData($data)
    {
        $error = [];
        $startDate = strpos($data['start_date'], '-');
        $endDate = strpos($data['end_date'], '-');
        if ($data['start_date']>$data['end_date']) {
            $error[] = __("End date can not be lesser then start From date.");
        } elseif ($data['amount_from']>=$data['amount_to']) {
            $error[] = __("Amount To can not be less then Amount From");
        } elseif ($data['amount_from']<0 || $data['amount_to']<0) {
            $error[] = __("Amount From or Amount To can not be less then 0");
        } elseif ($startDate == false) {
            $error[] = __("Please enter valid date for start From date.");
        } elseif ($endDate == false) {
            $error[] = __("Please enter valid date for End date.");
        }

        return $error;
    }
}
