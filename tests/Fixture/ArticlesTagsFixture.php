<?php
namespace Josegonzalez\Version\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesTagsFixture extends TestFixture
{
    public $table = 'articles_tags';

    /**
     * fields property
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
     * records property
     *
     * @var array
     */
    public $records = [
        ['article_id' => 1, 'tag_id' => 1, 'version_id' => 2, 'sort_order' => 1],
    ];
}
