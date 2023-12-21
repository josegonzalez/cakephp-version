<?php
declare(strict_types=1);

return [
    'articles' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'author_id' => ['type' => 'integer', 'null' => true],
            'version_id' => ['type' => 'integer', 'null' => true],
            'title' => ['type' => 'string', 'null' => true],
            'body' => 'text',
            'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
            'settings' => ['type' => 'json', 'null' => true],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    'articles_tags' => [
        'columns' => [
            'article_id' => ['type' => 'integer'],
            'tag_id' => ['type' => 'integer'],
            'version_id' => ['type' => 'integer', 'null' => true],
            'sort_order' => ['type' => 'integer', 'default' => 1],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['article_id', 'tag_id']]],
    ],
    'articles_tags_versions' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'version_id' => ['type' => 'integer'],
            'model' => ['type' => 'string', 'null' => false],
            'article_id' => ['type' => 'integer', 'null' => false],
            'tag_id' => ['type' => 'integer', 'null' => false],
            'field' => ['type' => 'string', 'null' => false],
            'content' => ['type' => 'text'],
            'custom_field' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'version' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'version_id' => ['type' => 'integer'],
            'model' => ['type' => 'string', 'null' => false],
            'foreign_key' => ['type' => 'integer', 'null' => false],
            'field' => ['type' => 'string', 'null' => false],
            'content' => ['type' => 'text'],
            'custom_field' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'versions_with_user' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'version_id' => ['type' => 'integer'],
            'user_id' => ['type' => 'integer'],
            'model' => ['type' => 'string', 'null' => false],
            'foreign_key' => ['type' => 'integer', 'null' => false],
            'field' => ['type' => 'string', 'null' => false],
            'content' => ['type' => 'text'],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
