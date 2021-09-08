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
namespace Webkul\RegionUpload\Model;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\ResourceConnection;

class SaveRegion
{

    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Undocumented function
     *
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        RegionFactory $regionFactory,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Module\ModuleResource $moduleResource
    ) {
        $this->regionFactory = $regionFactory;
        $this->_resource = $resourceConnection;
        $this->_moduleResource = $moduleResource;
    }

    /**
     * Save Region Code
     *
     * @param array $data
     * @param string $countryId
     * @return array $result
     */
    public function saveRegionCodeCsv($data, $countryId)
    {
        try {
            $regionModel = $this->regionFactory->create();
            $regionModel->setCountryId($countryId);
            $regionModel->setCode($data[0]);
            $regionModel->setDefaultName($data[1]);
            $regionModel->setIsManual(1);
            $regionId = $regionModel->save()->getId();
            $bind[] = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $data[1]];

            if (isset($data[2])) {
                $locales = json_decode($data[2], true);
                if (is_array($locales) && !empty($locales)) {
                    foreach ($locales as $value) {
                        $bind[] = [
                            'locale' => $value[0],
                            'region_id' => $regionId, 
                            'name' => $value[1]
                        ];
                    }
                }
            }
            
            $connection = $this->_moduleResource->getConnection();
            $connection->beginTransaction();
            $connection->insertMultiple($this->_resource->getTableName('directory_country_region_name'), $bind);
            $connection->commit();
            
            $result = [
                'error' => false
            ];
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Save region code
     *
     * @param array $param
     * @return array $result
     */
    public function saveRegion($param)
    {
        $countryId = $param['wk_country_id'];
        $code = $param['wk_region_code'];
        $name = $param['wk_region_name'];
        $isManual = 1;
        $data = [
            "country_id" => $countryId,
            "code" => $code,
            "default_name" => $name,
            "is_manual" => $isManual
        ];
        try {
            $regionColl = $this->regionFactory->create();
            $regionId = $regionColl->addData($data)->save()->getId();
            $bind[] = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $data['default_name']];
            if(!empty($param['regionname'])) {
                foreach ($param['regionname'] as $value) {
                    $bind[] = [
                        'locale' => $value['locale'],
                        'region_id' => $regionId, 
                        'name' => $value['name']
                    ];
                }
            }
            
            $connection = $this->_moduleResource->getConnection();
            $connection->beginTransaction();
            $connection->insertMultiple($this->_resource->getTableName('directory_country_region_name'), $bind);
            $connection->commit();

            $result = [
                'message' => __('Region Code Successfully Added.'),
                'error' => false
            ];
        } catch (\Exception $e) {
            $result = [
                'message' => $e->getMessage(),
                'error' => true
            ];
        }
        return $result;
    }
}
