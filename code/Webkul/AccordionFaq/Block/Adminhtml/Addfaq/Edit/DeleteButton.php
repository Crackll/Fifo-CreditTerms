<?php

namespace Webkul\AccordionFaq\Block\Adminhtml\Addfaq\Edit;

use Magento\Backend\Block\Widget\Context;


use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class FAQ DeleteButton
 */
class DeleteButton implements ButtonProviderInterface
{
    protected $request;

    protected $context;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->request->getParam('id')) {
            $data = [
                'label' => __('Delete FAQ'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->context->getUrlBuilder()
            ->getUrl('*/*/delete', ['id' => $this->request->getParam('id')]);
    }
}
