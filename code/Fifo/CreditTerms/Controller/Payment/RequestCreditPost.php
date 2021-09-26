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
        JsonHelper $jsonHelper
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->_jsonHelper = $jsonHelper;
        $this->creditTermsApplicationsFactory = $creditTermsApplicationsFactory;
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

                if(isset($params['buyer_credit_terms'])){
                    $termJsonDecode = $this->_jsonHelper->jsonDecode($params['buyer_credit_terms']);
                    $creditTermApp->setData('credit_term_category',$termJsonDecode['termCategory']);
                    $creditTermApp->setData('credit_term_days',$termJsonDecode['termDays']);
                    $creditTermApp->setData('credit_term_limit',$termJsonDecode['termLimit']);
                    $creditTermApp->setData('buyer_credit_terms',$termJsonDecode['termCategory']);
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
