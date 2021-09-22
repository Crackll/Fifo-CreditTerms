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

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;

class RequestCreditPost extends Action
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
        \Fifo\CreditTerms\Model\CreditTermsApplicationsFactory $creditTermsApplicationsFactory
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->creditTermsApplicationsFactory = $creditTermsApplicationsFactory;
        parent::__construct(
            $context
        );
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


                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'creditterms/payment/form',
                        [
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    );
                }

                $creditTermApp = $this->creditTermsApplicationsFactory->create();
                $creditTermApp->setData($params);
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
