<?php
declare(strict_types=1);

/**
 * Class ArticlesFixture
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\Fixture
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
namespace Josegonzalez\Version\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Class ArticlesFixture
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\Fixture
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class ArticlesFixture extends TestFixture
{
    public string $table = 'articles';

    /**
     * Records property
     *
     * @var array
     */
    public array $records = [
        ['author_id' => 1, 'version_id' => 2, 'title' => 'First Article Version 2', 'body' => 'First Article Body Version 2', 'published' => 'N'],
        ['author_id' => 2, 'version_id' => 3, 'title' => 'Second Article Version 3', 'body' => 'Second Article Body Version 3', 'published' => 'N'],
    ];
}
