<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Block\Adminhtml\Pricerule\Edit\Tab;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $mpWholeSaleHelper;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Webkul\MpWholesale\Helper\Data $mpWholeSaleHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\MpWholesale\Helper\Data $mpWholeSaleHelper,
        array $data = []
    ) {
        $this->mpWholeSaleHelper = $mpWholeSaleHelper;
        $this->session = $session;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pricerules_edit_tabs');
        $this->setTitle(__('Wholesaler Price Rule'));
    }
}
