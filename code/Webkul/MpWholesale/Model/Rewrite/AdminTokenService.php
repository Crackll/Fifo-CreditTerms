<?php
/**
 * Webkul MpWholesale Email Helper
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Model\Rewrite;

use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\User\Model\User as UserModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to handle token generation for Admins
 */
class AdminTokenService extends \Magento\Integration\Model\AdminTokenService
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    protected $tokenModelFactory;

    /**
     * User Model
     *
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    protected $validatorHelper;

    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    protected $tokenModelCollectionFactory;

    /**
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param UserModel $userModel
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Model\CredentialsValidator $validatorHelper
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        UserModel $userModel,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->userModel = $userModel;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
        parent::__construct(
            $tokenModelFactory,
            $userModel,
            $tokenModelCollectionFactory,
            $validatorHelper
        );
    }

    /**
     * Revoke token by admin id.
     *
     * The function will delete the token from the oauth_token table.
     *
     * @param int $adminId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeAdminAccessToken($adminId)
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByAdminId($adminId);
        if ($tokenCollection->getSize() == 0) {
            return true;
        }
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The tokens could not be revoked.'));
        }
        return true;
    }
}
