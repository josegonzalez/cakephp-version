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
    public string $table = 'articles_tags_versions';

    /**
     * Records property
     *
     * @var array
     */
    public array $records = [
        ['version_id' => 1, 'model' => 'ArticlesTags', 'article_id' => 1, 'tag_id' => 1, 'field' => 'sort_order', 'content' => 1],
        ['version_id' => 2, 'model' => 'ArticlesTags', 'article_id' => 1, 'tag_id' => 1, 'field' => 'sort_order', 'content' => 2],
    ];
}
