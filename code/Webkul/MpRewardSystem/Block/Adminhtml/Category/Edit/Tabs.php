<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var InlineInterface
     */
    protected $translateInline;

    /**
     * @param Context                                   $context
     * @param InlineInterface                           $translateInline
     * @param EncoderInterface                          $jsonEncoder
     * @param Session                                   $authSession
     * @param array                                     $data
     */
    public function __construct(
        Context $context,
        InlineInterface $translateInline,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    ) {
        $this->translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_points');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Reward Points On Category'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'rewardpointoncategory',
            [
                'label' => __('Reward Point On Category'),
                'content'=>$this->getLayout()->createBlock(
                    \Webkul\MpRewardSystem\Block\Adminhtml\Category\Edit\Tab\Form::class
                )->toHtml(),
                'class' => 'ajax'
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->translateInline->processResponseBody($html);
        return $html;
    }
}
