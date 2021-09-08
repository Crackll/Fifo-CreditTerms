<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRmaSystem\Controller\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;

class Printpdf extends \Magento\Framework\App\Action\Action
{

    /**
     * construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\MpRmaSystem\Model\Pdf $pdf
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Webkul\MpRmaSystem\Model\Pdf $pdf,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->pdf = $pdf;
        $this->logger = $logger;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $rmaId = $this->getRequest()->getParam('rma_id');
        try {
            $this->pdf->generatePdf($rmaId);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
