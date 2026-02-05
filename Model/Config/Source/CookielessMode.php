<?php

declare(strict_types=1);

namespace Indexa\Posthog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CookielessMode implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'always', 'label' => __('Cookieless always (default)')],
            ['value' => 'on_reject', 'label' => __('Cookieless until consent, then use cookies')],
            ['value' => 'off', 'label' => __('Use cookies')],
        ];
    }
}
