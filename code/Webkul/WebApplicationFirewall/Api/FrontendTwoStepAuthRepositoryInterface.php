<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface FrontendTwoStepAuthRepositoryInterface
{

    /**
     * Save FrontendTwoStepAuth
     * @param \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface $frontendTwoStepAuth
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface $frontendTwoStepAuth
    );

    /**
     * Retrieve FrontendTwoStepAuth
     * @param string $frontendtwostepauthId
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($frontendtwostepauthId);

    /**
     * Retrieve FrontendTwoStepAuth matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete FrontendTwoStepAuth
     * @param \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface $frontendTwoStepAuth
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface $frontendTwoStepAuth
    );

    /**
     * Delete FrontendTwoStepAuth by ID
     * @param string $frontendtwostepauthId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($frontendtwostepauthId);
}
