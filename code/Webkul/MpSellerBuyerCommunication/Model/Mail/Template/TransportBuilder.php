<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerBuyerCommunication
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
namespace Webkul\MpSellerBuyerCommunication\Model\Mail\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Zend\Mime\Mime;
use Zend\Mime\PartFactory;
 
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory = null,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->templateFactory = $templateFactory;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->partFactory = $objectManager->get(PartFactory::class);
        $this->productMetadata = $productMetadata;
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory
        );
        $this->messageFactory = $messageFactory ?:
        $this->objectManager->get(MessageInterfaceFactory::class);
        $this->message = $this->messageFactory->create();
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     */
    public function addCc($address, $name = '')
    {
        $version = $this->productMetadata->getVersion();
        if ($version>"2.3.2") {
            $this->addAddressByType('cc', $address, $name);
        } else {
            parent::addCc($address, $name);
        }
 
        return $this;
    }
 
    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addTo($address, $name = '')
    {
        $version = $this->productMetadata->getVersion();
        if ($version>"2.3.2") {
            $this->addAddressByType('to', $address, $name);
        } else {
            parent::addTo($address, $name);
        }
        return $this;
    }
 
    /**
     * Add bcc address
     *
     * @param array|string $address
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addBcc($address)
    {
        $version = $this->productMetadata->getVersion();
        if ($version>"2.3.2") {
            $this->addAddressByType('bcc', $address);
        } else {
            parent::addBcc($address);
        }
 
        return $this;
    }
 
    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setReplyTo($email, $name = null)
    {
        $version = $this->productMetadata->getVersion();
        if ($version>"2.3.2") {
            $this->addAddressByType('replyTo', $email, $name);
        } else {
            parent::setReplyTo($email, $name);
        }
 
        return $this;
    }
 
    /**
     * Set mail from address
     *
     * @param string|array $from
     *
     * @return $this
     * @throws InvalidArgumentException
     * @see setFromByScope()
     *
     * @deprecated 102.0.1 This function sets the from address but does not provide
     * a way of setting the correct from addresses based on the scope.
     */
    public function setFrom($from)
    {
        return $this->setFromByScope($from);
    }
 
    /**
     * Set mail from address by scopeId
     *
     * @param string|array $from
     * @param string|int $scopeId
     *
     * @return $this
     * @throws InvalidArgumentException
     * @throws MailException
     * @since 102.0.1
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $version = $this->productMetadata->getVersion();
        if ($version>"2.3.2") {
            $result = $this->_senderResolver->resolve($from, $scopeId);
            $this->addAddressByType('from', $result['email'], $result['name']);
        } elseif ($version == "2.3.0") {
            parent::setFrom($from);
        } else {
            parent::setFromByScope($from, $scopeId);
        }
 
        return $this;
    }
 
    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     *
     * @return $this
     */
    public function setTemplateIdentifier($templateIdentifier)
    {
        $this->templateIdentifier = $templateIdentifier;
 
        return $this;
    }
 
    /**
     * Set template model
     *
     * @param string $templateModel
     *
     * @return $this
     */
    public function setTemplateModel($templateModel)
    {
        $this->templateModel = $templateModel;
        return $this;
    }
 
    /**
     * Set template vars
     *
     * @param array $templateVars
     *
     * @return $this
     */
    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;
 
        return $this;
    }
 
    /**
     * Set template options
     *
     * @param array $templateOptions
     * @return $this
     */
    public function setTemplateOptions($templateOptions)
    {
        $this->templateOptions = $templateOptions;
 
        return $this;
    }
 
    /**
     * Get mail transport
     *
     * @return TransportInterface
     * @throws LocalizedException
     */
    public function getTransport()
    {
        try {
            $this->prepareMessage();
            $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        } finally {
            $this->reset();
        }
 
        return $mailTransport;
    }
 
    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset()
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }
 
    /**
     * Get template
     *
     * @return TemplateInterface
     */
    protected function getTemplate()
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }
  
    /**
     * Prepare message.
     *
     * @return $this
     * @throws LocalizedException if template type is unknown
     */
    protected function prepareMessage()
    {
        $version = $this->productMetadata->getVersion();
        if ($version > "2.3.2") {
            $template = $this->getTemplate();
            $content = $template->processTemplate();
            switch ($template->getType()) {
                case TemplateTypesInterface::TYPE_TEXT:
                    $part['type'] = \Magento\Framework\Mail\MimeInterface::TYPE_TEXT;
                    break;
    
                case TemplateTypesInterface::TYPE_HTML:
                    $part['type'] = \Magento\Framework\Mail\MimeInterface::TYPE_HTML;
                    break;
    
                default:
                    throw new LocalizedException(
                        new Phrase('Unknown template type')
                    );
            }
            $mimePartInterfaceFactory = $this->objectManager->create(
                \Magento\Framework\Mail\MimePartInterfaceFactory::class
            );
            $mimePart = $mimePartInterfaceFactory->create(['content' => $content]);
            $parts =
            isset($this->attachments) && count($this->attachments) ?
            array_merge([$mimePart], $this->attachments) : [$mimePart];
            $mimeMessageInterfaceFactory = $this->objectManager->create(
                \Magento\Framework\Mail\MimeMessageInterfaceFactory::class
            );
            $this->messageData['body'] = $mimeMessageInterfaceFactory->create(
                ['parts' => $parts]
            );
            // @codingStandardsIgnoreStart
            $this->messageData['subject'] = html_entity_decode(
                (string)$template->getSubject(),
                ENT_QUOTES
            );
            // @codingStandardsIgnoreEnd

            $emailMessageInterfaceFactory = $this->objectManager->create(
                \Magento\Framework\Mail\EmailMessageInterfaceFactory::class
            );
            $this->message = $emailMessageInterfaceFactory->create($this->messageData);
        } else {
            parent::prepareMessage();
        }
        return $this;
    }
 
    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param string|array $email
     * @param string|null $name
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        if (is_string($email)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            if (class_exists(\Magento\Framework\Mail\AddressConverter::class)) {
                $this->messageData[$addressType][] = $objectManager->create(
                    \Magento\Framework\Mail\AddressConverter::class
                )->convert($email, $name);
                return;
            } else {
                return;
            }
            
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        }
    }
 
    /**
     * @param string|null $content
     * @param string|null $fileName
     * @param string|null $fileType
     * @return TransportBuilder
     */
    public function addAttachment(?string $content, ?string $fileName, ?string $fileType)
    {
        $attachmentPart = $this->partFactory->create();
        $attachmentPart->setContent($content)
            ->setType($fileType)
            ->setFileName($fileName)
            ->setDisposition(Mime::DISPOSITION_ATTACHMENT)
            ->setEncoding(Mime::ENCODING_BASE64);
        $this->attachments[] = $attachmentPart;
 
        return $this;
    }
}
