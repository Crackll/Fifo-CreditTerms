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
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\RegionUpload\Model\SaveRegion;
use Magento\Framework\App\ResourceConnection;

class Save extends Action
{
    /**
     * Undocumented function
     *
     * @param Context $context
     * @param RegionFactory $regionFactory
     * @param SaveRegion $saveRegion
     */
    public function __construct(
        Context $context,
        RegionFactory $regionFactory,
        SaveRegion $saveRegion,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Module\ModuleResource $moduleResource
    ) {
        parent::__construct($context);
        $this->regionFactory = $regionFactory;
        $this->saveRegion = $saveRegion;
        $this->_moduleResource = $moduleResource;
        $this->_resource = $resourceConnection;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    // protected function _isAllowed()
    // {
    //     return 1;//$this->_authorization->isAllowed("Webkul_RegionUpload::region_upload");
    // }

    public function execute()
    {
        $param = $this->_request->getParams();
        
        if (!empty($param) && $param['region_id']) {
            $region = $this->regionFactory->create()->load($param['region_id']);
            $regionId = $param['region_id'];
            $countryId = $param['wk_country_id'];
            $name = $param['wk_region_name'];
            $isManual = 1;
            $data = [
                "country_id" => $countryId,
                "default_name" => $name,
            ];
            // try {
                $region->addData($data)->save();
                $this->delete($regionId);
                $bind[] = ['locale' => 'en_US', 
                    'region_id' => $regionId, 
                    'name' => $data['default_name']
                ];
                if(!empty($param['regionname'])) {
                    foreach ($param['regionname'] as $value) {
                        $bind[] = [
                            'locale' => $value['locale'],
                            'region_id' => $regionId, 
                            'name' => $value['name']
                        ];
                    }
                }
                // print_r($bind); die;
                $connection = $this->_moduleResource->getConnection();
                $connection->beginTransaction();
                $connection->insertMultiple($this->_resource->getTableName('directory_country_region_name'), $bind);
                $connection->commit();

                $result = [
                    'message' => __('Region Code Successfully Updated.'),
                    'error' => false
                ];
            // } catch (\Exception $e) {
            //     $result = [
            //         'message' => $e->getMessage(),
            //         'error' => true
            //     ];
            // }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('regionupload/view/index');
    }

    public function delete($id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('directory_country_region_name');
        $sql = "Delete FROM " . $tableName." Where region_id = ".$id;
        $connection->query($sql);
    }
}
