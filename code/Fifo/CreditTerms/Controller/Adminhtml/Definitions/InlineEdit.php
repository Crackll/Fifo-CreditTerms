<?php
/**
 * @category   Fifo
 * @package    Fifo_CreditTerms
 * @author     abdulmalik422@gmail.com
 * @copyright  This file was generated by using Module Creator(http://code.vky.co.in/magento-2-module-creator/) provided by VKY <viky.031290@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Fifo\CreditTerms\Controller\Adminhtml\Definitions;

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postDefinitions = $this->getRequest()->getParam('definitions', []);
            if (!count($postDefinitions)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postDefinitions) as $modelid) {
                    /** @var \Magento\Cms\Model\Block $block */
                    $model = $this->_objectManager->create('Fifo\CreditTerms\Model\CreditTerms')->load($modelid);
                    try {
                        $model->setData(array_merge($model->getData(), $postDefinitions[$modelid]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = "[Mytesting ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}