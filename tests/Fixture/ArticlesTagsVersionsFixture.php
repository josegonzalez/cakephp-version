<?php
declare(strict_types=1);

/**
 * Class ArticlesTagsVersionsFixture
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
 * Class ArticlesTagsVersionsFixture
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\Fixture
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class ArticlesTagsVersionsFixture extends TestFixture
{
    /**
     * Table property
     *
     * @var string
     */
    public $table = 'articles_tags_versions';

    /**
     * Fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'version_id' => ['type' => 'integer'],
        'model' => ['type' => 'string', 'null' => false],
        'article_id' => ['type' => 'integer', 'null' => false],
        'tag_id' => ['type' => 'integer', 'null' => false],
        'field' => ['type' => 'string', 'null' => false],
        'content' => ['type' => 'text'],
        'custom_field' => ['type' => 'text'],
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
        ['version_id' => 1, 'model' => 'ArticlesTags', 'article_id' => 1, 'tag_id' => 1, 'field' => 'sort_order', 'content' => 1],
        ['version_id' => 2, 'model' => 'ArticlesTags', 'article_id' => 1, 'tag_id' => 1, 'field' => 'sort_order', 'content' => 2],
    ];
}
