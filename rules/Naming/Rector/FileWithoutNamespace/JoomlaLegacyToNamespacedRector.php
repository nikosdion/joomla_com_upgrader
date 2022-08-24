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
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Config\JoomlaLegacyPrefixToNamespace;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix202208\Webmozart\Assert\Assert;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * A Rector rule to namespace legacy Joomla 3 MVC classes into Joomla 4+ MVC namespaced classes
 *
 * @see \Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaLegacyToNamespacedRector\JoomlaLegacyToNamespacedRectorTest
 */
final class JoomlaLegacyToNamespacedRector extends AbstractRector implements ConfigurableRectorInterface
{
	private const ACCEPTABLE_CONTAINMENT_FOLDERS = ['admin', 'administrator', 'backend', 'site', 'frontend', 'api'];

	/**
	 * @var JoomlaLegacyPrefixToNamespace[]
	 */
	private $legacyPrefixesToNamespaces = [];

	/**
	 * @var null|string
	 * @readonly
	 */
	private $newNamespace = null;

	/**
	 * @var RemovedAndAddedFilesCollector
	 * @readonly
	 */
	private $removedAndAddedFilesCollector;

	public function __construct(
		RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
	)
	{
		$this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
	}

	/**
	 * @param   mixed[]  $configuration
	 */
	public function configure(array $configuration): void
	{
		Assert::allIsAOf($configuration, JoomlaLegacyPrefixToNamespace::class);
		$this->legacyPrefixesToNamespaces = $configuration;
	}

	/**
	 * @return array<class-string<Node>>
	 */
	public function getNodeTypes(): array
	{
		return [
			FileWithoutNamespace::class, Namespace_::class,
		];
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition('Convert legacy Joomla 3 MVC class names into Joomla 4 namespaced ones.', [
			new CodeSample(
				<<<'CODE_SAMPLE'
/** @var FooModelBar $someModel */
$model = new FooModelBar;
CODE_SAMPLE
				, <<<'CODE_SAMPLE'
/** @var \Acme\Foo\BarModel $someModel */
$model = new BarModel;
CODE_SAMPLE
			),
		]);
	}

	/**
	 * @param   FileWithoutNamespace|Namespace_  $node
	 */
	public function refactor(Node $node): ?Node
	{
		$this->newNamespace = null;

		if ($node instanceof FileWithoutNamespace)
		{
			$changedStmts = $this->refactorStmts($node->stmts, true);

			if ($changedStmts === null)
			{
				return null;
			}

			$node->stmts = $changedStmts;

			// Add a new namespace?
			if ($this->newNamespace !== null)
			{
				return new Namespace_(new Name($this->newNamespace), $changedStmts);
			}
		}

		if ($node instanceof Namespace_)
		{
			return $this->refactorNamespace($node);
		}

		return null;
	}

	private function divineExtensionRootFolder(): ?string
	{
		$path     = str_replace('\\', '/', $this->file->getFilePath());
		$pathBits = explode('/', $path);

		for ($i = 0; $i < 3; $i++)
		{
			$lastPart = array_pop($pathBits);

			if ($lastPart === null)
			{
				return null;
			}

			$isComponent = substr($lastPart, 0, 4) !== 'com_';

			if ($isComponent || in_array($lastPart, self::ACCEPTABLE_CONTAINMENT_FOLDERS))
			{
				$pathBits[] = $lastPart;

				break;
			}
		}

		if (empty($pathBits))
		{
			return null;
		}

		return implode('/', $pathBits);
	}

	/**
	 * @return string
	 */
	private function getApplicationSide(): string
	{
		/**
		 * I need to find the parent folder of my file to see if it's one of admin, administrator, backend, site,
		 * frontend, api and decide which namespace suffix to add.
		 */
		// The full path to the current file, normalised as a UNIX path
		$fullPath = str_replace('\\', '/', $this->file->getFilePath());
		// Explode the path to an array
		$pathBits = explode('/', $fullPath);
		// This is the filename
		array_pop($pathBits);
		// Remove the immediate folder we are in, I can infer it from the classname, duh
		array_pop($pathBits);
		// Get the parent folder
		$parentFolder = array_pop($pathBits);

		// If the parent folder starts with com_ I will get its parent instead
		if (substr($parentFolder, 0, 4) === 'com_')
		{
			$parentFolder = array_pop($pathBits);
		}

		switch (strtolower(trim($parentFolder)))
		{
			case 'admin':
			case 'administrator':
			case 'backend':
				return 'Administrator';

			case 'site':
			case 'frontend':
			default:
				return 'Site';

			case 'api':
				return 'Api';
		}
	}

	private function legacyClassNameToNamespaced(string $legacyClassName, string $prefix, string $newNamespace, bool $isNewFile = false): string
	{
		$applicationSide = $this->getApplicationSide();

		// Controller, Model and Table are pretty straightforward
		$legacySuffixes = ['Controller', 'Model', 'Table'];

		foreach ($legacySuffixes as $legacySuffix)
		{
			$fullLegacyPrefix = $prefix . $legacySuffix;

			if ($legacyClassName === $fullLegacyPrefix)
			{
				if ($legacySuffix !== 'Controller')
				{
					return $legacyClassName;
				}

				// If the file already has a namespace go away. We have already refactored it.
				if (!$isNewFile)
				{
					return $legacyClassName;
				}

				$legacyClassName = $fullLegacyPrefix . 'Display';
			}

			if (strpos($legacyClassName, $fullLegacyPrefix) !== 0)
			{
				continue;
			}

			// Convert FooModelBar => BarModel
			$bareName = ucfirst(strtolower(substr($legacyClassName, strlen($fullLegacyPrefix)))) . $legacySuffix;

			$fqn = trim($newNamespace, '\\')
				. '\\' . $applicationSide
				. '\\' . $legacySuffix
				. '\\' . $bareName;

			return $fqn;
		}

		$fullLegacyPrefix = $prefix . 'View';

		if (strpos($legacyClassName, $fullLegacyPrefix) !== 0)
		{
			return $legacyClassName;
		}

		// The full path to the current file, normalised as a UNIX path
		$fullPath = str_replace('\\', '/', $this->file->getFilePath());
		// Explode the path to an array
		$pathBits = explode('/', $fullPath);
		// This is the filename
		$filename = array_pop($pathBits);
		/**
		 * Strip the 'view.' prefix and '.php' suffix from the filename, add 'View' to it. This changes a filename
		 * view.html.php into the HtmlView classname.
		 */
		$leafClassName = ucfirst(strtolower(str_replace(['view.', '.php'], ['', ''], $filename))) . 'View';

		// FooViewBar => Bar\HtmlView
		$bareName = ucfirst(strtolower(substr($legacyClassName, strlen($fullLegacyPrefix)))) . '\\' . $leafClassName;
		$fqn      = trim($newNamespace, '\\')
			. '\\' . $applicationSide
			. '\\View'
			. '\\' . $bareName;

		return $fqn;
	}

	private function moveFile($newNamespacePrefix, $fqn)
	{
		// I also need to move the file
		$thisSideRoot = $this->divineExtensionRootFolder();

		if ($thisSideRoot === null)
		{
			return;
		}

		// Remove the common namespace prefix
		$newNamespacePrefix = trim($newNamespacePrefix, '\\');
		$fqn                = trim($fqn, '\\');

		if (strpos($newNamespacePrefix, $fqn) !== 0)
		{
			// Whatever happened is massively wrong. Give up.
			return;
		}

		$relativeName = trim(substr($fqn, strlen($newNamespacePrefix)), '\\');

		$newPath = implode(
			DIRECTORY_SEPARATOR,
			explode('\\', $relativeName)
		);

		if ($this->file->getFilePath() === $newPath)
		{
			// Okay, this is already in the correct PSR-4 folder. Bye-bye!
			return;
		}

		// Move the file
		$this->removedAndAddedFilesCollector->addMovedFile($this->file, $newPath);
	}

	private function processIdentifier(Identifier $identifier, string $prefix, string $newNamespacePrefix, bool $isNewFile = false): ?Identifier
	{
		$parentNode = $identifier->getAttribute(AttributeKey::PARENT_NODE);

		if (!$parentNode instanceof Class_)
		{
			return null;
		}

		$name = $this->getName($identifier);

		if ($name === null)
		{
			return null;
		}

		$newNamespace    = '';
		$lastNewNamePart = $name;
		$fqn             = $this->legacyClassNameToNamespaced($name, $prefix, $newNamespacePrefix, $isNewFile);

		if ($fqn === $name)
		{
			return $identifier;
		}

		$bits = explode('\\', $fqn);

		if (count($bits) > 1)
		{
			$lastNewNamePart = array_pop($bits);
			$newNamespace    = implode('\\', $bits);
		}

		if ($this->newNamespace !== null && $this->newNamespace !== $newNamespace)
		{
			throw new ShouldNotHappenException('There cannot be 2 different namespaces in one file');
		}

		$this->newNamespace = $newNamespace;
		$identifier->name   = $lastNewNamePart;

		$this->moveFile($newNamespacePrefix, $fqn);

		return $identifier;
	}

	private function processName(Name $name, string $prefix, string $newNamespace, bool $isNewFile = false): Name
	{
		// The class name
		$legacyClassName = $this->getName($name);

		$fqn = $this->legacyClassNameToNamespaced($legacyClassName, $prefix, $newNamespace, $isNewFile);

		if ($fqn === $legacyClassName)
		{
			return $name;
		}

		$name->parts = explode('\\', $fqn);

		return $name;
	}

	/**
	 * @param   \PhpParser\Node\Name|\PhpParser\Node\Identifier  $node
	 *
	 * @return Identifier|Name|null
	 */
	private function processNameOrIdentifier($node, bool $isNewFile = false): ?Node
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
				$prefix . 'Controller*',
				$prefix . 'Model*',
				$prefix . 'View*',
				$prefix . 'Table*',
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

	private function refactorNamespace(Namespace_ $namespace): ?Namespace_
	{
		$changedStmts = $this->refactorStmts($namespace->stmts);

		if ($changedStmts === null)
		{
			return null;
		}

		return $namespace;
	}

	private function refactorStmts(array $stmts, bool $isNewFile = false): ?array
	{
		$hasChanged = \false;

		$this->traverseNodesWithCallable($stmts, function (Node $node) use (&$hasChanged, $isNewFile): ?Node {
			if (
				!$node instanceof Name
				&& !$node instanceof Identifier
				&& !$node instanceof Property
				&& !$node instanceof FunctionLike
			)
			{
				return null;
			}

			//if ($this->refactorPhpDoc($node)) {
			//    $hasChanged = \true;
			//}

			if (
				$node instanceof Name
				|| $node instanceof Identifier
			)
			{
				$changedNode = $this->processNameOrIdentifier($node, $isNewFile);

				if ($changedNode instanceof Node)
				{
					$hasChanged = \true;

					return $changedNode;
				}
			}

			return null;
		});

		if ($hasChanged)
		{
			return $stmts;
		}

		return null;
	}
}
