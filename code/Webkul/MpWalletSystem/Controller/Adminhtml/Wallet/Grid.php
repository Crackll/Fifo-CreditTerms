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

namespace Webkul\MpWalletSystem\Controller\Adminhtml\Wallet;

use Webkul\MpWalletSystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Framework\Controller\Result\RawFactory;

/**
 * Webkul MpWalletSystem Controller
 */
class Grid extends WalletController
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * Initialize dependencies
     *
     * @param MagentoBackendAppActionContext          $context
     * @param MagentoFrameworkViewResultLayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \Webkul\MpWalletSystem\Block\Adminhtml\Wallet\Edit\Tab\Grid::class,
                'walletcustomergrid'
            )->toHtml()
        );
    }
}
