<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Fifo\CreditTerms\Controller\Payment;

use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use \Magento\Framework\Json\Helper\Data as JsonHelper;

class RequestCreditPost extends Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    private $creditTermsApplicationsFactory;

    private $buyerCreditTermFactory;

    protected $_jsonHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param \Fifo\CreditTerms\Model\CreditTermsApplicationsFactory $creditTermsApplicationsFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Fifo\CreditTerms\Model\CreditTermsApplicationsFactory $creditTermsApplicationsFactory,
        \Fifo\CreditTerms\Model\CreditTermsFactory $buyerCreditTermFactory,
        JsonHelper $jsonHelper
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->_jsonHelper = $jsonHelper;
        $this->creditTermsApplicationsFactory = $creditTermsApplicationsFactory;
        $this->buyerCreditTermFactory = $buyerCreditTermFactory;
        parent::__construct(
            $context
        );
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    private function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * RequestQuotePost action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Redirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            try {
                $params = $this->getRequest()->getParams();

//                if (!$this->formKeyValidator->validate($this->getRequest())) {
//                    return $this->resultRedirectFactory->create()->setPath(
//                        'creditterms/payment/form',
//                        [
//                            '_secure' => $this->getRequest()->isSecure()
//                        ]
//                    );
//                }

                $creditTermApp = $this->creditTermsApplicationsFactory->create();

                $creditTermApp->setData('email',$params['email']);
                $creditTermApp->setData('cr_number',$params['cr_number']);
                $creditTermApp->setData('company_name',$params['company_name']);
                $creditTermApp->setData('restaurant_name',$params['restaurant_name']);
                $creditTermApp->setData('number_of_branches',$params['number_of_branches']);
                $creditTermApp->setData('region',$params['region']);
                $creditTermApp->setData('location',$params['location']);
                $creditTermApp->setData('owner_name',$params['owner_name']);
                $creditTermApp->setData('contact_person_name',$params['contact_person_name']);
                $creditTermApp->setData('contact_number',$params['contact_number']);
                $creditTermApp->setData('contact_email',$params['contact_email']);
                $creditTermApp->setData('contact_email',$params['contact_email']);
                $creditTermApp->setData('created_at',date('Y-m-d H:i:s'));

                if(isset($params['buyer_credit_terms'])){
                    $buyerCollection = $this->buyerCreditTermFactory->create()->getCollection()
                        ->addFieldToSelect(['terms_name','payment_terms','credit_limit'])
                        ->addFieldToFilter("creditterms_definition_id", $params['buyer_credit_terms'])
                        ->addFieldToFilter("type", \Fifo\CreditTerms\Model\Source\TypeOptions::BUYER_TYPE)->getLastItem()->getData();
                    $creditTermApp->setData('credit_term_category',$buyerCollection['terms_name']);
                    $creditTermApp->setData('credit_term_days',$buyerCollection['payment_terms']);
                    $creditTermApp->setData('credit_term_limit',$buyerCollection['credit_limit']);
                    $creditTermApp->setData('buyer_credit_terms',$buyerCollection['creditterms_definition_id']);
                }

                $id = $creditTermApp->save()->getId();

                if($id){
                    $this->messageManager->addSuccess(__('Credit Application is successfully submitted.'));
                    return $this->resultRedirectFactory->create()->setPath(
                        'creditterms/payment/form',
                        [
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    );
                }

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            return $this->resultRedirectFactory->create()->setPath(
                'creditterms/payment/form',
                [
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'creditterms/payment/form',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
