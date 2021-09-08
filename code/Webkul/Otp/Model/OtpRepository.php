<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Otp\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Webkul\Otp\Api\OtpRepositoryInterface;

class OtpRepository implements OtpRepositoryInterface
{
    /**
     * @var Otp
     */
    private $otpModel;

    /**
     * @param Otp $otp
     */
    public function __construct(
        Otp $otpModel
    ) {
        $this->otpModel = $otpModel;
    }

    /**
     * @param \Webkul\Otp\Api\Data\OtpInterface $otp
     *
     * @throws CouldNotSaveException
     */
    public function save(\Webkul\Otp\Api\Data\OtpInterface $otp)
    {
        try {
            $this->otpModel->save($otp);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the page: %1', $e->getMessage()),
                $e
            );
        }
    }

    /**
     * @param $string customer Email
     *
     * @return Array customer data
     *
     * @throws couldnotdeleteException
     */
    public function getByEmail($customerEmail)
    {
        $collection = $this->otpModel->load($customerEmail, 'email');
        return $collection;
    }

    /**
     * @param string customer Email
     *
     * @throws couldnotdeleteException
     */
    public function deleteByEmail($customerEmail)
    {
        try {
            $collection = $this->getByEmail($customerEmail);
            $collection->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not save the page: %1', $e->getMessage()),
                $e
            );
        }
    }
}
