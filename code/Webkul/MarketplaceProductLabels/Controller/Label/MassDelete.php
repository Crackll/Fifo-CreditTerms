<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Controller\Label;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\MarketplaceProductLabels\Model\LabelFactory as LabelFactory;
use Magento\Customer\Model\UrlFactory as UrlFactory;

/**
 * Webkul Marketplace Product MassDelete controller.
 */
class MassDelete extends Action
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var HelperData
     */
    protected $helper;

     /**
      * @var HelperData
      */
    protected $labelFactory;

    /**
     * @var LabelFactory
     */
    protected $urlFactory;

    /**
     * @param Context $context
     * @param SessionFactory $customerSessionFactory
     * @param HelperData $helper
     * @param LabelFactory $labelFactory
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSessionFactory,
        HelperData $helper,
        LabelFactory $labelFactory,
        UrlFactory $urlFactory
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->helper = $helper;
        $this->labelFactory = $labelFactory;
        $this->urlFactory = $urlFactory;
        parent::__construct(
            $context
        );
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
        $loginUrl = $this->urlFactory->create()->getLoginUrl();

        if (!$this->customerSessionFactory->create()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }
    
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $isPartner = $this->helper->isSeller();
            if ($isPartner == 1) {
                try {
                    $wholedata = $this->getRequest()->getParams();

                    $ids = $this->getRequest()->getParam('labels_mass_delete');

                    $sellerId = $this->helper->getCustomerId();
                    $deletedIdsArr = [];
                    $sellerLabels = $this->labelFactory
                    ->create()->getCollection()
                    ->addFieldToFilter(
                        'id',
                        ['in' => $ids]
                    )->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    foreach ($sellerLabels as $sellerLabel) {
                        array_push($deletedIdsArr, $sellerLabel['id']);
                        $wholedata['id'] = $sellerLabel['id'];
                        $this->_eventManager->dispatch(
                            'mp_delete_label',
                            [$wholedata]
                        );
                    }
                    $count = 0;
                    foreach ($deletedIdsArr as $id) {
                        try {
                                $label = $this->loadLabel($id);
                                $this->removeItem($label);
                                $count++;
                        } catch (\Exception $e) {
                            $this->messageManager->addError($e->getMessage());
                        }
                    }
                    $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/labellist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/labeltlist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/labellist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Delete Label
     *
     * @param [type] $item
     * @return void
     */
    public function removeItem($item)
    {
        $item->delete();
    }

    /**
     *  Load Label
     *
     * @param [type] $id
     * @return label
     */
    public function loadLabel($id)
    {
        $label = $this->labelFactory->create()->load($id);
        return $label;
    }
}
