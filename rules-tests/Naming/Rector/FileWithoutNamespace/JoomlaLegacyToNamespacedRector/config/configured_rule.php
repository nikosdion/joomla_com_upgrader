<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Naming\Rector\FileWithoutNamespace\JoomlaLegacyToNamespacedRector::class);
};
