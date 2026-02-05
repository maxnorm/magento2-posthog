<?php

/**
 * Copyright © Indexa. All rights reserved.
 */

declare(strict_types=1);

namespace Indexa\Posthog\Block;

use Indexa\Posthog\Helper\Data as PosthogHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Script extends Template
{
    /**
     * @var PosthogHelper
     */
    private $posthogHelper;

    /**
     * @param Context $context
     * @param PosthogHelper $posthogHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        PosthogHelper $posthogHelper,
        array $data = []
    ) {
        $this->posthogHelper = $posthogHelper;
        parent::__construct($context, $data);
    }

    public function shouldRender(): bool
    {
        return $this->posthogHelper->isConfigured();
    }

    public function getProjectApiKey(): string
    {
        return $this->posthogHelper->getProjectApiKey();
    }

    public function getApiHost(): string
    {
        return $this->posthogHelper->getApiHost();
    }

    public function getPersonProfiles(): string
    {
        return $this->posthogHelper->getPersonProfiles();
    }

    public function getCookielessMode(): string
    {
        return $this->posthogHelper->getCookielessMode();
    }
}
