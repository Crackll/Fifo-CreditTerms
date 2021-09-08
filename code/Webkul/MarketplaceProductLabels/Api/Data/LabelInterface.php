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
namespace Webkul\MarketplaceProductLabels\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * MarketplaceProductLabels Label interface.
 * @api
 */
interface LabelInterface extends ExtensibleDataInterface
{
    /**
     * @var String
     */
    const ID = 'id';

    /**
     * @var String
     */
    const NAME = 'name';

    /**
     * @var String
     */
    const IMAGENAME = 'image_name';

    /**
     * @var String
     */
    const POSITION  = 'position';

    /**
     * @var String
     */
    const STATUS = 'status';

    /**
     * @var String
     */
    const SELLERID = 'seller_id';
    
    /**
     * Get label_name
     * @return string|null
     */
    public function getLabelName();

    /**
     * Set label_name
     * @param string $name
     * @return \Webkul\MarketplaceProductLabels\Api\Data\LabelInterface
     */
    public function setLabelName($labelName);

    /**
     * Get position
     * @return string|null
     */
    public function getPosition();

    /**
     * Set position
     * @param string $name
     * @return \Webkul\MarketplaceProductLabels\Api\Data\LabelInterface
     */
    public function setPosition($position);

    /**
     * Get image_name
     * @return string|null
     */
    public function getImageName();

    /**
     * Set image_name
     * @param string $name
     * @return \Webkul\MarketplaceProductLabels\Api\Data\LabelInterface
     */
    public function setImageName($imageName);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $name
     * @return \Webkul\MarketplaceProductLabels\Api\Data\LabelInterface
     */
    public function setStatus($status);

    /**
     * Get seller_id
     * @return string|null
     */
    public function getSellerId();

    /**
     * Set seller_id
     * @param string $name
     * @return \Webkul\MarketplaceProductLabels\Api\Data\LabelInterface
     */
    public function setSellerId($selerId);
}
