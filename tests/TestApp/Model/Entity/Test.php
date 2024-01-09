<?php
declare(strict_types=1);

/**
 * Class Test
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\TestCase\Model\Entity
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */

namespace Josegonzalez\Version\Test\TestApp\Model\Entity;

use Cake\ORM\Entity;
use Josegonzalez\Version\Model\Behavior\Version\VersionTrait;

/**
 * Class Test
 *
 * A test entity to test with.
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\TestCase\Model\Behavior
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class Test extends Entity
{
    use VersionTrait;
}
