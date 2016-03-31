<?php
/**
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @since         1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Josegonzalez\Version\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Class TranslateFixture
 *
 */
class VersionsFixture extends TestFixture
{
    /**
     * table property
     *
     * @var string
     */
    public $table = 'version';

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'version_id' => ['type' => 'integer'],
        'model' => ['type' => 'string', 'null' => false],
        'foreign_key' => ['type' => 'integer', 'null' => false],
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
