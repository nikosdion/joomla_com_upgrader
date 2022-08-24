<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaLegacyToNamespacedRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JoomlaLegacyToNamespacedRectorTest extends AbstractRectorTestCase
{
	protected function setUp(): void
	{
		require_once __DIR__ . '/../../../../../override/Symplify/EasyTesting/StaticFixtureSplitter.php';

		parent::setUp();
	}

	public function provideConfigFilePath(): string
	{
		return __DIR__ . '/config/configured_rule.php';
	}

	/**
	 * @return \Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
	 */
	public function provideData(): \Iterator
	{
		return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
	}

	/**
	 * @dataProvider provideData()
	 */
	public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
	{
		$this->doTestFileInfo($fileInfo);
	}
}
