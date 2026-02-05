<?php

/**
 * Copyright © Indexa. All rights reserved.
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Indexa_Posthog',
    __DIR__
);
