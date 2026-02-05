<?php

declare(strict_types=1);

namespace Indexa\Posthog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PersonProfiles implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'always', 'label' => __('Always')],
            ['value' => 'identified_only', 'label' => __('Identified Only')],
            ['value' => 'never', 'label' => __('Never')],
        ];
    }
}
