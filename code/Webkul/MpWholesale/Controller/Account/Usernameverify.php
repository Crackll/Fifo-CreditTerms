<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\User\Model\ResourceModel\User;

/**
 * Webkul MpWholesaler Usernameverify controller.
 */
class Usernameverify extends Action
{
    /**
     * @var Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var User
     */

    protected $adminUser;

    /**
     * @var \Webkul\MpWholesale\Helper
     */

    protected $mpWholeSaleHelper;

    /**
     * @param Context $context
     * @param User $adminUser
     * @param \Webkul\MpWholesale\Helper\Data $mpWholeSaleHelper
     */
    public function __construct(
        Context $context,
        User $adminUser,
        \Webkul\MpWholesale\Helper\Data $mpWholeSaleHelper
    ) {
        $this->adminUser = $adminUser;
        $this->mpWholeSaleHelper = $mpWholeSaleHelper;
        parent::__construct($context);
    }

    /**
     * Verify username exists or not action.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['username'])) {
            $model = $this->adminUser->loadByUsername($params['username']);
            if ($model) {
                $this->getResponse()->representJson(
                    $this->mpWholeSaleHelper->jsonEncodeData(1)
                );
            } else {
                $this->getResponse()->representJson(
                    $this->mpWholeSaleHelper->jsonEncodeData(0)
                );
            }
        } else {
            $this->getResponse()->representJson(
                $this->mpWholeSaleHelper->jsonEncodeData(0)
            );
        }
    }
}
