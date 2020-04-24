<?php
declare(strict_types=1);

/**
 * Class ArticlesTagsFixture
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
 * Class ArticlesTagsFixture
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\Fixture
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class ArticlesTagsFixture extends TestFixture
{
    public $table = 'articles_tags';

    /**
     * Fields property
     *
     * @var array
     */
    public $fields = [
        'article_id' => ['type' => 'integer'],
        'tag_id' => ['type' => 'integer'],
        'version_id' => ['type' => 'integer', 'null' => true],
        'sort_order' => ['type' => 'integer', 'default' => 1],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['article_id', 'tag_id']]]
    ];

    /**
     * Records property
     *
     * @var array
     */
    public $records = [
        ['article_id' => 1, 'tag_id' => 1, 'version_id' => 2, 'sort_order' => 1],
    ];
}
