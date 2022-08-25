<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare (strict_types=1);

namespace Rector\Naming\Rector\FileWithoutNamespace;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * A Rector rule to namespace legacy Joomla 3 Helper classes into Joomla 4+ MVC namespaced classes
 *
 * @since  1.0.0
 * @see    \Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaLegacyMVCToJ4Rector\JoomlaLegacyToNamespacedRectorTest
 */
final class JoomlaHelpersToJ4Rector extends JoomlaLegacyMVCToJ4Rector implements ConfigurableRectorInterface
{
	use JoomlaNamespaceHandlingTrait;

	/**
	 * Get the rule definition.
	 *
	 * This was used to generate the initial test fixture.
	 *
	 * @return  RuleDefinition
	 * @throws  \Symplify\RuleDocGenerator\Exception\PoorDocumentationException
	 * @since   1.0.0
	 */
	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition('Convert legacy Joomla 3 Helper class names into Joomla 4 namespaced ones.', [
			new CodeSample(
				<<<'CODE_SAMPLE'
abstract class HelloWorldHelper extends \Joomla\CMS\Helper\ContentHelper
{
}
CODE_SAMPLE
				, <<<'CODE_SAMPLE'
namespace Acme\Example\Administrator\Helper;

abstract class HelloworldHelper extends \Joomla\CMS\Helper\ContentHelper
{
}
CODE_SAMPLE
			),
		]);
	}

	/**
	 * Processes an Identifier node
	 *
	 * @param   Identifier  $identifier          The node to process
	 * @param   string      $prefix              The legacy Joomla 3 prefix, e.g. Example
	 * @param   string      $newNamespacePrefix  The Joomla 4 common namespace prefix e.g. \Acme\Example
	 * @param   bool        $isNewFile           Is this a file without a namespace already defined?
	 *
	 * @return  Identifier|null  The refactored identified; null if no refactoring is necessary / possible
	 * @throws  ShouldNotHappenException  A file had two classes in it yielding different namespaces. Don't do that!
	 * @since   1.0.0
	 */

	/**
	 * Process a Name or Identifier node but only if necessary!
	 *
	 * @param   Name|Identifier  $node  The node to possibly refactor
	 *
	 * @return  Identifier|Name|null  The refactored node; NULL if no refactoring was necessary / possible.
	 * @since   1.0.0
	 */
	protected function processNameOrIdentifier($node, bool $isNewFile = false): ?Node
	{
		// no name → skip
		if ($node->toString() === '')
		{
			return null;
		}

		foreach ($this->legacyPrefixesToNamespaces as $legacyPrefixToNamespace)
		{
			$prefix    = $legacyPrefixToNamespace->getNamespacePrefix();
			$supported = [
				$prefix . 'Helper*',
				$prefix . '*Helper',
			];

			if (!$this->isNames($node, $supported))
			{
				continue;
			}

			$excludedClasses = $legacyPrefixToNamespace->getExcludedClasses();

			if ($excludedClasses !== [] && $this->isNames($node, $excludedClasses))
			{
				return null;
			}

			if ($node instanceof Name)
			{
				return $this->processName($node, $prefix, $legacyPrefixToNamespace->getNewNamespace(), $isNewFile);
			}

			return $this->processIdentifier($node, $prefix, $legacyPrefixToNamespace->getNewNamespace(), $isNewFile);
		}

		return null;
	}
}
