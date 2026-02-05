<?php

/**
 * Copyright © Indexa. All rights reserved.
 */

declare(strict_types=1);

namespace Indexa\Posthog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    private const XML_PATH_ENABLED = 'posthog/general/enabled';
    private const XML_PATH_PROJECT_API_KEY = 'posthog/general/project_api_key';
    private const XML_PATH_API_HOST = 'posthog/general/api_host';
    private const XML_PATH_PERSON_PROFILES = 'posthog/general/person_profiles';
    private const XML_PATH_COOKIELESS_MODE = 'posthog/general/cookieless_mode';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(Context $context, EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getProjectApiKey(?int $storeId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PROJECT_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($value === null || $value === '') {
            return '';
        }
        $value = (string) $value;
        try {
            $decrypted = $this->encryptor->decrypt($value);
            return $decrypted !== null ? $decrypted : $value;
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function getApiHost(?int $storeId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_API_HOST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value !== null ? (string) $value : '';
    }

    public function getPersonProfiles(?int $storeId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PERSON_PROFILES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value !== null ? (string) $value : 'identified_only';
    }

    public function getCookielessMode(?int $storeId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_COOKIELESS_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value !== null ? (string) $value : 'always';
    }

    public function isConfigured(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->getProjectApiKey($storeId) !== ''
            && $this->getApiHost($storeId) !== '';
    }
}
