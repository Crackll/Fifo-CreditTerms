<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Block\Rewrite;

/**
 * Backend menu block
 */
class Menu extends \Magento\Backend\Block\Menu
{
    /**
     * @var string
     */
    protected $_containerRenderer;

    /**
     * @var string
     */
    protected $_itemRenderer;

    /**
     * Backend URL instance
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * Current selected item
     *
     * @var \Magento\Backend\Model\Menu\Item|false|null
     */
    protected $_activeItemModel = null;

    /**
     * @var \Magento\Backend\Model\Menu\Filter\IteratorFactory
     */
    protected $_iteratorFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menuConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Backend\Block\MenuItemChecker
     */
    protected $menuItemChecker;

    /**
     * @var \Magento\Backend\Block\AnchorRenderer
     */
    protected $anchorRenderer;

    /**
     * @param Template\Context $context
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Backend\Model\Menu\Config $menuConfig
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     * @param \Magento\Backend\Block\MenuItemChecker|null $menuItemChecker
     * @param \Magento\Backend\Block\AnchorRenderer|null $anchorRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Model\Menu\Config $menuConfig,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = [],
        \Magento\Backend\Block\MenuItemChecker $menuItemChecker = null,
        \Magento\Backend\Block\AnchorRenderer $anchorRenderer = null
    ) {
        parent::__construct(
            $context,
            $url,
            $iteratorFactory,
            $authSession,
            $menuConfig,
            $localeResolver,
            $data,
            $menuItemChecker,
            $anchorRenderer
        );
        $this->menuItemChecker =  $menuItemChecker;
        $this->anchorRenderer = $anchorRenderer;
    }
    /**
     * Render Navigation
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @param int $limit
     * @param array $colBrakes
     * @return string HTML
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function renderNavigation($menu, $level = 0, $limit = 0, $colBrakes = [])
    {
        $role = $this->_authSession->getUser()->getRole();
        $itemPosition = 1;
        $outputStart = '<ul ' . (0 == $level ? 'id="nav" role="menubar"' : 'role="menu"') . ' >';
        $output = '';
        $actionArray = [
            'mpwholesale/unit',
            'mpwholesale/pricerule',
            'mpwholesale/quotation',
            'mpwholesale/leads'
        ];
        /** @var $menuItem \Magento\Backend\Model\Menu\Item  */
        foreach ($this->_getMenuIterator($menu) as $menuItem) {
            if ($role->getRoleName() != 'Wholesaler') {
                if (in_array($menuItem->getAction(), $actionArray)) {
                    continue;
                }
            }
            $menuId = $menuItem->getId();
            $itemName = substr($menuId, strrpos($menuId, '::') + 2);
            $itemClass = str_replace('_', '-', strtolower($itemName));

            if (is_array($colBrakes)
                && count($colBrakes)
                && $colBrakes[$itemPosition]['colbrake']
                && $itemPosition != 1) {
                $output .= '</ul></li><li class="column"><ul role="menu">';
            }

            $id = $this->getJsId($menuItem->getId());
            $subMenu = $this->_addSubMenu($menuItem, $level, $limit, $id);
            $anchor = $this->anchorRenderer->renderAnchor($this->getActiveItemModel(), $menuItem, $level);
            $output .= '<li ' . $this->getUiId($menuItem->getId())
                . ' class="item-' . $itemClass . ' ' . $this->_renderItemCssClass($menuItem, $level)
                . ($level == 0 ? '" id="' . $id . '" aria-haspopup="true' : '')
                . '" role="menu-item">' . $anchor . $subMenu . '</li>';
            $itemPosition++;
        }

        if (is_array($colBrakes) && count($colBrakes) && $limit) {
            $output = '<li class="column"><ul role="menu">' . $output . '</ul></li>';
        }

        return $outputStart . $output . '</ul>';
    }
}
