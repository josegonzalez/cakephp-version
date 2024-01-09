<?php
declare(strict_types=1);

/**
 * Class VersionsFixture
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
 * Class VersionsFixture
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\Fixture
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class VersionsFixture extends TestFixture
{
    /**
     * Table property
     *
     * @var string
     */
    public string $table = 'version';

    /**
     * Records property
     *
     * @var array
     */
    public array $records = [
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'author_id', 'content' => 1],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'title', 'content' => 'First Article'],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'body', 'content' => 'First Article Body'],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'published', 'content' => 'Y'],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'author_id', 'content' => 1, 'custom_field' => 'foo'],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'title', 'content' => 'First Article Version 2', 'custom_field' => 'foo'],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'body', 'content' => 'First Article Body Version 2', 'custom_field' => 'foo'],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'published', 'content' => 'N', 'custom_field' => 'foo'],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'author_id', 'content' => 2],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'title', 'content' => 'Second Article version 1'],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'body', 'content' => 'Second Article Body'],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'published', 'content' => 'Y'],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'author_id', 'content' => 2],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'title', 'content' => 'Second Article version 2'],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'body', 'content' => 'Second Article Body'],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'published', 'content' => 'Y'],
        ['version_id' => 3, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'author_id', 'content' => 2],
        ['version_id' => 3, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'title', 'content' => 'Second Article version 3'],
        ['version_id' => 3, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'body', 'content' => 'Second Article Body'],
        ['version_id' => 3, 'model' => 'Articles', 'foreign_key' => 2, 'field' => 'published', 'content' => 'Y'],
    ];
}
