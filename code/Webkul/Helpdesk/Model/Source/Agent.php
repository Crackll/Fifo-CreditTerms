<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Agent implements OptionSourceInterface
{
    /**
     * @var \Webkul\Helpdesk\Model\Type
     */
    protected $adminUserFactory;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(\Magento\User\Model\UserFactory $adminUserFactory)
    {
        $this->adminUserFactory = $adminUserFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $userColl = $this->adminUserFactory->create()->getCollection()
                               ->addFieldToFilter("is_active", ["neq"=>0]);

        $options = [];
        foreach ($userColl as $key => $user) {
            $options[] = [
                'label' => $user['firstname']." ".$user['lastname'],
                'value' => $user['user_id'],
            ];
        }
        return $options;
    }
}
