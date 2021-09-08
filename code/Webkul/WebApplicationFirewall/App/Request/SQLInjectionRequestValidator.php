<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
declare(strict_types=1);

namespace Webkul\WebApplicationFirewall\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\App\Request\ValidatorInterface;

/**
 * Validate request for being CSRF protected.
 * TODO
 */
class SQLInjectionRequestValidator implements ValidatorInterface
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory $redirectFactory
     * @param AppState $appState
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        AppState $appState
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->appState = $appState;
    }

    /**
     * Validate given request.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return bool
     */
    private function validateRequest(
        HttpRequest $request,
        ActionInterface $action
    ): bool {
        $valid = true;

        return $valid;
    }

    /**
     * Create exception for when incoming request failed validation.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return InvalidRequestException
     */
    private function createException(
        HttpRequest $request,
        ActionInterface $action
    ): InvalidRequestException {
        $exception = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $exception = $action->createCsrfValidationException($request);
        }
        if (!$exception) {
            $response = $this->redirectFactory->create()
                ->setRefererOrBaseUrl()
                ->setHttpResponseCode(302);
            $messages = [
                new Phrase('Invalid Form Key. Please refresh the page.'),
            ];
            $exception = new InvalidRequestException($response, $messages);
        }

        return $exception;
    }

    /**
     * @inheritDoc
     */
    public function validate(
        RequestInterface $request,
        ActionInterface $action
    ): void {
        try {
            $areaCode = $this->appState->getAreaCode();
        } catch (LocalizedException $exception) {
            $areaCode = null;
        }
        if ($request instanceof HttpRequest
            && in_array(
                $areaCode,
                [Area::AREA_FRONTEND, Area::AREA_ADMINHTML],
                true
            )
        ) {
            $valid = $this->validateRequest($request, $action);
            if (!$valid) {
                throw $this->createException($request, $action);
            }
        }
    }
}
