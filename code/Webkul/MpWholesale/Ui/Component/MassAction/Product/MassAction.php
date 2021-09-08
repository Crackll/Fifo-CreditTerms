<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Ui\Component\MassAction\Product;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class WholeSale MassAction
 */
class MassAction implements JsonSerializable
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;

    /**
     * Label name for Confimation
     *
     * @var string
     */
    protected $confirmLabel;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $getRequest;

    /**
     * @param \Magento\Framework\App\RequestInterface $getRequest
     * @param UrlInterface $urlBuilder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $getRequest,
        UrlInterface $urlBuilder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->getRequest = $getRequest;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
        $this->authSession = $authSession;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $userId =  $this->getRequest->getParam('user_id');
        if ($this->options === null) {
            $role = $this->authSession->getUser()->getRole();
            if ($role->getRoleName() == 'Wholesaler') {
                $options[0]['value']=1;
                $options[0]['label']="Delete";
                $options[1]['value']=0;
                $options[1]['label']="Enable";
                $options[2]['value']=2;
                $options[2]['label']="Disable";
            } else {
                $options[0]['value']=3;
                $options[0]['label']="Approve";
                $options[1]['value']=4;
                $options[1]['label']="Reject";
            }

            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'postid_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value'],'user_id'=>$userId]
                    );
                }
                
                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
                
                    $this->options[$optionCode['value']]['confirm'] = [
                        'title'=>$optionCode['label'],
                        'message' => $this->confirmLabel['message']
                    ];
                
            }
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                case 'confirm':
                    $this->confirmLabel = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
