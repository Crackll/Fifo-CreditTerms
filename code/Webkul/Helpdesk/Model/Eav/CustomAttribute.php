<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model\Eav;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Helpdesk CustomAttribute Model
 *
 * @method \Webkul\Helpdesk\Model\Eav\CustomAttribute _getResource()
 */
class CustomAttribute extends \Magento\Eav\Model\Entity\Attribute
{
    /**
     * Prepare data for save
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function beforeSave()
    {
        if ($this->getFrontendInput()=="image") {
            $this->setBackendModel(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class);
            $this->setBackendType('varchar');
        }

        if ($this->getFrontendInput()=="date") {
            $this->setBackendModel(\Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class);
            $this->setBackendType('datetime');
        }

        if ($this->getFrontendInput()=="textarea") {
            $this->setBackendType('text');
        }

        if ($this->getFrontendInput()=="text") {
            $this->setBackendType('varchar');
        }

        if ($this->getFrontendInput()=="file") {
            $this->setBackendType('varchar');
        }

        if (($this->getFrontendInput()=="multiselect" || $this->getFrontendInput()=="select")) {
            $this->setData('source_model', \Magento\Eav\Model\Entity\Attribute\Source\Table::class);
            $this->setBackendType('varchar');
        }

        if ($this->getFrontendInput()=="boolean") {
            $this->setData('source_model', \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class);
            $this->setBackendType('int');
            $this->setFrontendInput("select");
        }

        return parent::beforeSave();
    }
}
