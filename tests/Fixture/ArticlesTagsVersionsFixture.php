<?php
namespace Josegonzalez\Version\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesTagsVersionsFixture extends TestFixture
{
    /**
     * table property
     *
     * @var string
     */
    public $table = 'articles_tags_versions';

    /**
     * fields property
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
     * records property
     *
     * @var array
     */
    public $records = [
        ['version_id' => 1, 'model' => 'ArticlesTags', 'article_id' => 1, 'tag_id' => 1, 'field' => 'sort_order', 'content' => 1],
        ['version_id' => 2, 'model' => 'ArticlesTags', 'article_id' => 1, 'tag_id' => 1, 'field' => 'sort_order', 'content' => 2],
    ];
}
