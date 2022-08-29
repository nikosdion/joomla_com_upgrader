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
use Rector\Naming\Rector\FileWithoutNamespace\JoomlaFormFieldsRector;
use Rector\Naming\Rector\FileWithoutNamespace\JoomlaHtmlHelpersRector;
use Rector\Naming\Rector\FileWithoutNamespace\RenamedClassHandlerService;

return static function (RectorConfig $rectorConfig): void {
	$services = $rectorConfig
		->services()
		->defaults()
		->autowire()
		->autoconfigure();

	$services->set(RenamedClassHandlerService::class)
	         ->arg('$directory', realpath(__DIR__ . '/../../../../../../'));

	$rectorConfig->ruleWithConfiguration(
		JoomlaFormFieldsRector::class,
		[
			new JoomlaLegacyPrefixToNamespace('Example', '\\Acme\\Example', []),
		]
	);
};
