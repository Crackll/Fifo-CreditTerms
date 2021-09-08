<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface WholeSalerUnitRepositoryInterface
{
    public function save(\Webkul\MpWholesale\Api\Data\WholeSalerUnitInterface $item);

    public function getById($id);

    public function delete(\Webkul\MpWholesale\Api\Data\WholeSalerUnitInterface $item);

    public function deleteById($id);
}
