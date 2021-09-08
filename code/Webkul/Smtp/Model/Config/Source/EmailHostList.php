<?php

/** Webkul Smtp host list model
 * @category  Webkul
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */


/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Webkul\Smtp\Model\Config\Source;

class EmailHostList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => '', 'label' => __('Select SMTP Provider')],
                ['value' => 'zsmtp.hybridzimbra.com', 'label' => __('Zimbra')],
                ['value' => 'smtp.gmail.com', 'label' => __('Gmail')],
                ['value' => 'amazon-ses', 'label' => __('Amazon SES')],
                ['value' => 'smtp-mail.outlook.com', 'label' => __('Hotmail')],
                ['value' => 'smtp.mail.yahoo.com', 'label' => __('Yahoo')],
                ['value' => 'mail.gmx.com', 'label' => __('Gmx')],
                ['value' => 'smtp.mail.com', 'label' => __('Mail')],
                ['value' => 'smtp.sparkpostmail.com', 'label' => __('Spark Post')],
                ['value' => 'smtp.sendgrid.net', 'label' => __('SendGrid')],
                ['value' => 'smtp.zoho.com', 'label' => __('Zoho')],
                ['value' => 'custom', 'label' => __('Custom')]
            ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'zsmtp.hybridzimbra.com' => __('Zimbra'),
            'smtp.gmail.com' => __('Gmail'),
            'amazon-ses' => __('Amazon SES')
        ];
    }
}
