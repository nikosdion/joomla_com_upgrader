<?php
/**
 * Joomla 3 Component Upgrade Rectors
 *
 * @copyright  2022 Nicholas K. Dionysopoulos
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Rector\Naming\Rector\FileWithoutNamespace;

class JoomlaConstants
{

	/**
	 * The acceptable folder names where component files can be placed in.
	 *
	 * @since 1.0.0
	 * @var   string[]
	 */
	public const ACCEPTABLE_CONTAINMENT_FOLDERS = ['admin', 'administrator', 'backend', 'site', 'frontend', 'api'];
}