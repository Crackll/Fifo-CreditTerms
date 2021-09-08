<?php
/**
 * Webkul_Smtp
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Smtp\Plugin\Mail\Template;

class TransportBuilderByStorePlugin
{
    /**
     * @var \Webkul\Smtp\Model\Store
     */
    private $storeModel;

    /**
     * Sender resolver.
     *
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @param \Webkul\Smtp\Model\Store $storeModel
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     */
    public function __construct(
        \Webkul\Smtp\Model\Store $storeModel,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
    ) {
        $this->storeModel = $storeModel;
        $this->senderResolver = $senderResolver;
    }
    public function beforeSetFromByStore(
        \Magento\Framework\Mail\Template\TransportBuilderByStore $subject,
        $from,
        $store
    ) {
        if (!$this->storeModel->getStoreId()) {
            $this->storeModel->setStoreId($store);
        }
        $email = $this->senderResolver->resolve($from, $store);
        $this->storeModel->setFrom($email);
        return [$from, $store];
    }
}
