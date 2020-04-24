<?php
declare(strict_types=1);

/**
 * Class VersionsWithUserFixture
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
 * Class VersionsWithUserFixture
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\Fixture
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class VersionsWithUserFixture extends TestFixture
{
    /**
     * Table property
     *
     * @var string
     */
    public $table = 'versions_with_user';

    /**
     * Fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'version_id' => ['type' => 'integer'],
        'user_id' => ['type' => 'integer'],
        'model' => ['type' => 'string', 'null' => false],
        'foreign_key' => ['type' => 'integer', 'null' => false],
        'field' => ['type' => 'string', 'null' => false],
        'content' => ['type' => 'text'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * Records property
     *
     * @var array
     */
    public $records = [
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'author_id', 'content' => 1, 'user_id' => 2],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'title', 'content' => 'First Article', 'user_id' => 2],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'body', 'content' => 'First Article Body', 'user_id' => 2],
        ['version_id' => 1, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'published', 'content' => 'Y', 'user_id' => 2],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'author_id', 'content' => 1, 'custom_field' => 'foo', 'user_id' => 3],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'title', 'content' => 'First Article Version 2', 'custom_field' => 'foo', 'user_id' => 3],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'body', 'content' => 'First Article Body Version 2', 'custom_field' => 'foo', 'user_id' => 3],
        ['version_id' => 2, 'model' => 'Articles', 'foreign_key' => 1, 'field' => 'published', 'content' => 'N', 'custom_field' => 'foo', 'user_id' => 3],
    ];
}
