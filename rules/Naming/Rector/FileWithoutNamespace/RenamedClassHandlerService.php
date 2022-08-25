<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Rector\Naming\Rector\FileWithoutNamespace;

/**
 * Automatically save the renamed classes, sorted by component side (admin, site).
 */
final class RenamedClassHandlerService
{
	/**
	 * The directory where the _classmap.json file will be stored into.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $directory;

	/**
	 * The temporary instance of the class map
	 *
	 * @since 1.0.0
	 * @var   array[]
	 */
	private $map = [
		'site'  => [],
		'admin' => [],
	];

	/**
	 * Public constructor
	 *
	 * @param   string  $directory  The directory of the _classmap.json file
	 *
	 * @since   1.0.0
	 */
	public function __construct(string $directory)
	{
		$this->directory = $directory;

		$this->load();
	}

	/**
	 * Called on service destruction. Auto-saves the class map file.
	 *
	 * @since  1.0.0
	 */
	public function __destruct()
	{
		$this->save();
	}

	/**
	 * Adds an entry into the class map
	 *
	 * @param   string  $legacyClass      The legacy class which we renamed from.
	 * @param   string  $namespacedClass  The FQN of the namespaced class we renamed to.
	 * @param   string  $namespacePrefix  The namespace prefix we used.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function addEntry(string $legacyClass, string $namespacedClass, string $namespacePrefix)
	{
		$prefix   = trim($namespacePrefix, '\\');
		$tempName = trim($namespacedClass, '\\');

		if (strpos($tempName, $prefix) !== 0)
		{
			return;
		}

		$tempName = trim(substr($tempName, strlen($prefix)), '\\');
		$parts    = explode('\\', $tempName);

		if (!in_array($parts[0], ['Administrator', 'Site']))
		{
			return;
		}

		$side = $parts[0] === 'Site' ? 'site' : 'admin';

		$this->map[$side][$legacyClass] = $namespacedClass;
	}

	/**
	 * Load the already saved class map from _classmap.json
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function load()
	{
		$filePath = $this->directory . '/_classmap.json';

		if (!is_file($filePath))
		{
			return;
		}

		$contents  = file_get_contents($filePath);
		$this->map = @json_decode($contents, true) ?? [
			'site'  => [],
			'admin' => [],
		];
	}

	/**
	 * Saved the class map into _classmap.json
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function save()
	{
		$filePath = $this->directory . '/_classmap.json';
		$contents = json_encode($this->map);

		file_put_contents($filePath, $contents);
	}
}