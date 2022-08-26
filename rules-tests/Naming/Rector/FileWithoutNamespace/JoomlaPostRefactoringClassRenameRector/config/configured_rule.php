<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare (strict_types=1);

namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Naming\Config\JoomlaLegacyPrefixToNamespace;
use Rector\Naming\Rector\FileWithoutNamespace\JoomlaLegacyMVCToJ4Rector;
use Rector\Naming\Rector\FileWithoutNamespace\RenamedClassHandlerService;
use Rector\Naming\Rector\JoomlaPostRefactoringClassRenameRector;

return static function (RectorConfig $rectorConfig): void {
	$services = $rectorConfig
		->services()
		->defaults()
		->autowire()
		->autoconfigure();

	$services->set(RenamedClassHandlerService::class)
	         ->arg('$directory', realpath(__DIR__ . '/../'));

	$rectorConfig->rule(JoomlaPostRefactoringClassRenameRector::class);
};
