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
    public $table = 'articles';

    /**
     * Fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer', 'null' => true],
        'version_id' => ['type' => 'integer', 'null' => true],
        'title' => ['type' => 'string', 'null' => true],
        'body' => 'text',
        'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
        'published_date' => ['type' => 'datetime', 'null' => true],
        'settings' => ['type' => 'json', 'null' => true],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    /**
     * Records property
     *
     * @var array
     */
    public $records = [
        [
            'author_id' => 1,
            'version_id' => 2,
            'title' => 'First Article Version 2',
            'body' => 'First Article Body Version 2',
            'published' => 'N',
            'published_date' => 'N;'
        ],
        [
            'author_id' => 2,
            'version_id' => 3,
            'title' => 'Second Article Version 3',
            'body' => 'Second Article Body Version 3',
            'published' => 'N',
            'published_date' => 'O:20:"Cake\I18n\FrozenTime":3:{s:4:"date";s:26:"2022-01-05 10:56:23.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}'
        ],
    ];
}
