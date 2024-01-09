<?php
declare(strict_types=1);

/**
 * Class VersionBehaviorTest
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\TestCase\Model\Behavior
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */

namespace Josegonzalez\Version\Test\TestCase\Model\Behavior;

use Cake\Event\EventManager;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use Josegonzalez\Version\Model\Behavior\VersionBehavior;
use Josegonzalez\Version\Test\TestApp\Model\Entity\Test;
use ReflectionObject;

/**
 * Class VersionBehaviorTest
 * The tests for this package.
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Test\TestCase\Model\Behavior
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class VersionBehaviorTest extends TestCase
{
    /**
     * The fixtures to load.
     *
     * @var array
     */
    public array $fixtures = [
        'plugin.Josegonzalez/Version.Versions',
        'plugin.Josegonzalez/Version.VersionsWithUser',
        'plugin.Josegonzalez/Version.Articles',
        'plugin.Josegonzalez/Version.ArticlesTagsVersions',
        'plugin.Josegonzalez/Version.ArticlesTags',
    ];

    /**
     * Save a new version
     *
     * @return void
     */
    public function testSaveNew()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
                'entityClass' => Test::class,
            ],
        );
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        $this->assertEquals(2, $article->version_id);

        $versionTable = $this->getTableLocator()->get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();
        $this->assertCount(8, $results);

        $article->title = 'Titulo';
        $table->save($article);

        $versionTable = $this->getTableLocator()->get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();

        $this->assertEquals(3, $article->version_id);
        $this->assertCount(13, $results);
    }

    /**
     * Find a specific version.
     *
     * @return void
     */
    public function testFindVersionSpecific()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        $version = $article->version(1);

        $this->assertEquals('First Article', $version->get('title'));
    }

    /**
     * Find a specific version.
     *
     * @return void
     */
    public function testFindVersionAdditional()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $config = [
            'additionalVersionFields' => [
                'version_foo' => 'foooo',
                'baaar',
            ],
        ];
        $table->addBehavior('Josegonzalez/Version.Version', $config);
        $article = $table->find('all')->first();
        $version = $article->version(1);

        $version = $version->toArray();
        $this->assertArrayHasKey('version_foo', $version);
        $this->assertArrayHasKey('version_baaar', $version);
    }

    /**
     * Find versions of an entity.
     *
     * @return void
     */
    public function testFindVersions()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();

        $versions = $article->versions();
        $this->assertCount(2, $versions);
        $this->assertEquals('First Article Version 2', $versions[2]->title);
        $versions = $article->versions();
        $this->assertCount(2, $versions);
        $this->assertEquals('First Article Version 2', $versions[2]->title);

        $article->title = 'Capitulo';
        $table->save($article);

        $versions = $article->versions();
        $this->assertCount(2, $versions);
        $this->assertEquals('First Article Version 2', $versions[2]->title);
        $versions = $article->versions(true);
        $this->assertCount(3, $versions);
        $this->assertEquals('Capitulo', $versions[3]->title);

        $article->title = 'Titulo';
        $table->save($article);

        $versions = $article->versions();
        $this->assertCount(3, $versions);
        $this->assertEquals('Capitulo', $versions[3]->title);

        $versions = $article->versions(true);
        $this->assertCount(4, $versions);
        $this->assertEquals('Titulo', $versions[4]->title);
    }

    /**
     * Save with limited fields.
     *
     * @return void
     */
    public function testSaveLimitFields()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version', ['fields' => 'title']);
        $article = $table->find('all')->first();

        $article->title = 'Titulo';
        $article->body = 'Hello world!';
        $table->save($article);

        $versionTable = $this->getTableLocator()->get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id, 'version_id' => 3])
            ->enableHydration(false)
            ->toArray();

        $this->assertCount(1, $results);
        $this->assertEquals('title', $results[0]['field']);
    }

    /**
     * Save only dirty fields.
     *
     * @return void
     */
    public function testSaveDirtyFields()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version', ['onlyDirty' => true]);
        $article = $table->find('all')->first();

        $article->title = 'Titulo';
        $article->body = 'Hello world!';
        $table->save($article);

        $versionTable = $this->getTableLocator()->get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id, 'version_id' => 3])
            ->enableHydration(false)
            ->toArray();

        $this->assertCount(2, $results);
        $this->assertEquals('title', $results[0]['field']);
        $this->assertEquals('body', $results[1]['field']);
    }

    /**
     * Find with limited fields.
     *
     * @return void
     */
    public function testFindVersionLimitFields()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version', ['fields' => 'title']);
        $article = $table->find('all')->first();
        $version = $article->version(1);

        $this->assertArrayHasKey('title', $version);
        $this->assertArrayNotHasKey('body', $version);
    }

    /**
     * Save with valid custom field (meta data like user_id)
     *
     * @return void
     */
    public function testSaveWithValidMetaData()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        EventManager::instance()->on(
            'Model.Version.beforeSave',
            function ($event) {
                return [
                    'custom_field' => 'bar',
                ];
            }
        );
        $versionTable = $this->getTableLocator()->get('Version');

        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();
        $this->assertEquals('foo', $results[4]['custom_field']);

        $article->title = 'Titulo';
        $table->save($article);

        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();
        $this->assertEquals('bar', $results[9]['custom_field']);
    }

    /**
     * Save with invalid custom field (meta data like user_id)
     *
     * @return void
     */
    public function testSaveWithInvalidMetaData()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        EventManager::instance()->on(
            'Model.Version.beforeSave',
            function ($event) {
                return [
                    'nonsense' => 'bar',
                ];
            }
        );
        $versionTable = $this->getTableLocator()->get('Version');

        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();
        $this->assertEquals('foo', $results[4]['custom_field']);

        $article->title = 'Titulo';
        $table->save($article);

        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();
        $this->assertNull($results[9]['custom_field']);
    }

    /**
     * Find version with composite keys (e.g. ArticlesTagsVersions)
     *
     * @return void
     */
    public function testFindWithCompositeKeys()
    {
        $table = $this->getTableLocator()->get(
            'ArticlesTags',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior(
            'Josegonzalez/Version.Version',
            [
            'fields' => 'sort_order',
            'versionTable' => 'articles_tags_versions',
            'foreignKey' => ['article_id', 'tag_id'],
            ]
        );

        $entity = $table->find()->first();
        $this->assertEquals(['sort_order' => 1, 'version_id' => 1, 'version_created' => null], $entity->version(1)->toArray());
        $this->assertEquals(['sort_order' => 2, 'version_id' => 2, 'version_created' => null], $entity->version(2)->toArray());
    }

    /**
     * Save with composite keys (e.g. ArticlesTagsVersions)
     *
     * @return void
     */
    public function testSaveWithCompositeKeys()
    {
        $table = $this->getTableLocator()->get(
            'ArticlesTags',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior(
            'Josegonzalez/Version.Version',
            [
            'fields' => 'sort_order',
            'versionTable' => 'articles_tags_versions',
            'foreignKey' => ['article_id', 'tag_id'],
            ]
        );

        $entity = $table->find()->first();
        $entity->sort_order = 3;
        $table->save($entity);
        //$this->assertEquals(3, $entity->version_id);
        $this->assertEquals(3, $entity->version_id);
        $this->assertEquals(['sort_order' => 3, 'version_id' => 3, 'version_created' => null], $entity->version(3)->toArray());
    }

    /**
     * Get the custom fields like user_id (additional meta data)
     *
     * @return void
     */
    public function testGetAdditionalMetaData()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior(
            'Josegonzalez/Version.Version',
            [
            'versionTable' => 'versions_with_user',
            'additionalVersionFields' => ['created', 'user_id'],
            ]
        );
        $table->find('all')->first();

        $this->getTableLocator()->get('Version', ['table' => 'versions_with_user']);

        $results = $table->find('versions')->toArray();

        $this->assertSame(2, $results[0]['_versions'][1]['version_user_id']);
        $this->assertSame(3, $results[0]['_versions'][2]['version_user_id']);
    }

    /**
     * Associations correctly names / recognized?
     *
     * @return void
     */
    public function testAssociations()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version');

        $this->assertTrue($table->associations()->has('ArticleVersion'));
        $versions = $table->getAssociation('ArticleVersion');
        $this->assertInstanceOf('Cake\Orm\Association\HasMany', $versions);
        $this->assertEquals('__version', $versions->getProperty());

        $this->assertTrue($table->associations()->has('ArticleBodyVersion'));
        $bodyVersions = $table->getAssociation('ArticleBodyVersion');
        $this->assertInstanceOf('Cake\Orm\Association\HasMany', $bodyVersions);
        $this->assertEquals('body_version', $bodyVersions->getProperty());
    }

    /**
     * Get a specific version id
     *
     * @return void
     */
    public function testGetVersionId()
    {
        // init test data
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->where(['version_id' => 2])->first();
        $article->title = 'First Article Version 3';
        $table->save($article);

        // action in controller receiving outdated data
        $table->patchEntity($article, ['version_id' => 2]);

        $this->assertEquals(2, $article->version_id);
        $this->assertEquals(3, $table->getVersionId($article));
    }

    /**
     * Tests saving a non scalar db type, such as JSON
     *
     * @return void
     */
    public function testSaveNonScalarType()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $schema = $table->getSchema();
        $schema->setColumnType('settings', 'json');
        $table->setSchema($schema);
        $table->addBehavior('Josegonzalez/Version.Version');

        $data = ['test' => 'array'];
        $article = $table->get(1);
        $article->settings = $data;
        $table->saveOrFail($article);

        $version = $article->version($article->version_id);
        $this->assertSame($data, $version->settings);
    }

    /**
     * Tests versions convert types
     *
     * @return void
     */
    public function testVersionConvertsType()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $table->addBehavior('Josegonzalez/Version.Version');

        $article = $table->get(1);
        $version = $article->version($article->version_id);
        $this->assertIsInt($version->author_id);
    }

    /**
     * Tests _convertFieldsToType
     *
     * @return void
     */
    public function testConvertFieldsToType()
    {
        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $schema = $table->getSchema();
        $schema->setColumnType('settings', 'json');
        $table->setSchema($schema);
        $behavior = new VersionBehavior($table);

        $reflection = new ReflectionObject($behavior);
        $method = $reflection->getMethod('convertFieldsToType');
        $method->setAccessible(true);

        $data = ['test' => 'array'];
        $fields = [
            'settings' => json_encode($data),
            'author_id' => '1',
            'body' => 'text',
        ];
        $fields = $method->invokeArgs($behavior, [$fields, 'toPHP']);
        $this->assertIsArray($fields['settings']);
        $this->assertSame($data, $fields['settings']);
        $this->assertIsInt($fields['author_id']);
        $this->assertIsString('string', $fields['body']);

        $data = ['test' => 'array'];
        $fields = [
            'settings' => ['test' => 'array'],
            'author_id' => 1,
            'body' => 'text',
        ];
        $fields = $method->invokeArgs($behavior, [$fields, 'toDatabase']);
        $this->assertIsString($fields['settings']);
        $this->assertSame(json_encode($data), $fields['settings']);
        $this->assertIsInt($fields['author_id']);
        $this->assertIsString($fields['body']);
    }

    /**
     * Tests passing an invalid direction to _convertFieldsToType
     *
     * @return void
     */
    public function testConvertFieldsToTypeInvalidDirection()
    {
        $this->expectException(InvalidArgumentException::class);

        $table = $this->getTableLocator()->get(
            'Articles',
            [
            'entityClass' => Test::class,
            ]
        );
        $behavior = new VersionBehavior($table);

        $reflection = new ReflectionObject($behavior);
        $method = $reflection->getMethod('convertFieldsToType');
        $method->setAccessible(true);

        $method->invokeArgs($behavior, [[], 'invalidDirection']);
    }
}
