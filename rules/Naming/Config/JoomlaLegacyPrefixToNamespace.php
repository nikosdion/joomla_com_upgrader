<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare (strict_types=1);

namespace Rector\Naming\Config;

final class JoomlaLegacyPrefixToNamespace
{
	private $namespacePrefix;

	private $newNamespace;

	private $excludedClasses = [];

	/**
	 * @param string[] $excludedClasses
	 */
	public function __construct(string $namespacePrefix, string $newNamespace, array $excludedClasses = [])
	{
		$this->namespacePrefix = $namespacePrefix;
		$this->newNamespace = $newNamespace;
		$this->excludedClasses = $excludedClasses;
	}
	public function getNamespacePrefix() : string
	{
		return $this->namespacePrefix;
	}
	/**
	 * @return string[]
	 */
	public function getExcludedClasses() : array
	{
		return $this->excludedClasses;
	}

	/**
	 * @return string
	 */
	public function getNewNamespace(): string
	{
		return $this->newNamespace;
	}
}