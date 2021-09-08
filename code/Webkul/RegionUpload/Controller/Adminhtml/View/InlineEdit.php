<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RegionUpload\Controller\Adminhtml\View;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends Action implements HttpPostActionInterface
{
    /**
     * Undocumented function
     *
     * @param Context $context
     * @param RegionFactory $regionFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, RegionFactory $regionFactory, JsonFactory $resultJsonFactory)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this
            ->_authorization
            ->isAllowed("Webkul_RegionUpload::region_upload");
    }

    public function execute()
    {
        $param = $this->getRequest()
            ->getParam('items', []);
        $resultJson = $this
            ->resultJsonFactory
            ->create();
        try {
            if (count($param)) {
                foreach (array_keys($param) as $id) {
                    $code = $param[$id]['code'];
                    $name = $param[$id]['default_name'];
                    $regionId = $param[$id]['region_id'];

                    $regionColl = $this
                        ->regionFactory
                        ->create();
                    $regionCollection = $regionColl->load($regionId);
                    $regionCollection->setCode($code);
                    $regionCollection->setDefaultName($name);
                    $regionCollection->save($name);
                }
                return $resultJson->setData(['messages' => [__('Region code updated.') ], 'error' => false, ]);
            }

        } catch (\Exception $e) {
            return $resultJson->setData(['messages' => [__('Region code not updated.') ],
            'error' => $e->getMessage() , ]);
        }
    }
}
