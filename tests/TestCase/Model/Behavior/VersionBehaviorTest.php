<?php
namespace Josegonzalez\Version\Test\TestCase\Model\Behavior;

use Cake\Collection\Collection;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Josegonzalez\Version\Model\Behavior\VersionBehavior;
use Josegonzalez\Version\Model\Behavior\Version\VersionTrait;

class TestEntity extends Entity
{
    use VersionTrait;
}

class VersionBehaviorTest extends TestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'plugin.Josegonzalez\Version.versions',
        'plugin.Josegonzalez\Version.versions_with_user',
        'plugin.Josegonzalez\Version.articles',
        'plugin.Josegonzalez\Version.articles_tags_versions',
        'plugin.Josegonzalez\Version.articles_tags',
    ];

    /**
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
    }

    /**
     * @return void
     */
    public function testSaveNew()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        $this->assertEquals(2, $article->version_id);

        $versionTable = TableRegistry::get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();
        $this->assertCount(8, $results);

        $article->title = 'Titulo';
        $table->save($article);

        $versionTable = TableRegistry::get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id])
            ->enableHydration(false)
            ->toArray();

        $this->assertEquals(3, $article->version_id);
        $this->assertCount(12, $results);
    }

    /**
     * @return void
     */
    public function testFindVersion()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        $version = $article->version(1);

        $this->assertEquals('First Article', $version->get('title'));
    }

    /**
     * @return void
     */
    public function testFindVersionX()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity',
        ]);
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
     * @return void
     */
    public function testFindVersions()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
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
     * @return void
     */
    public function testSaveLimitFields()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version', ['fields' => 'title']);
        $article = $table->find('all')->first();

        $article->title = 'Titulo';
        $article->body = 'Hello world!';
        $table->save($article);

        $versionTable = TableRegistry::get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id, 'version_id' => 3])
            ->enableHydration(false)
            ->toArray();

        $this->assertCount(1, $results);
        $this->assertEquals('title', $results[0]['field']);
    }

    /**
     * @return void
     */
    public function testSaveDirtyFields()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version', ['onlyDirty' => true]);
        $article = $table->find('all')->first();

        $article->title = 'Titulo';
        $article->body = 'Hello world!';
        $table->save($article);

        $versionTable = TableRegistry::get('Version');
        $results = $versionTable->find('all')
            ->where(['foreign_key' => $article->id, 'version_id' => 3])
            ->enableHydration(false)
            ->toArray();

        $this->assertCount(2, $results);
        $this->assertEquals('title', $results[0]['field']);
        $this->assertEquals('body', $results[1]['field']);
    }

    /**
     * @return void
     */
    public function testFindVersionLimitFields()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version', ['fields' => 'title']);
        $article = $table->find('all')->first();
        $version = $article->version(1);

        $this->assertArrayHasKey('title', $version);
        $this->assertArrayNotHasKey('body', $version);
    }

    /**
     * @return void
     */
    public function testSaveWithValidMetaData()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        EventManager::instance()->on(
            function ($event) {
                return [
                    'custom_field' => 'bar',
                ];
            },
            'Model.Version.beforeSave'
        );
        $versionTable = TableRegistry::get('Version');

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
     * @return void
     */
    public function testSaveWithInvalidMetaData()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        EventManager::instance()->on(
            function ($event) {
                return [
                    'nonsense' => 'bar',
                ];
            },
            'Model.Version.beforeSave'
        );
        $versionTable = TableRegistry::get('Version');

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
     * @return void
     */
    public function testFindWithCompositeKeys()
    {
        $table = TableRegistry::get('ArticlesTags', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version', [
            'fields' => 'sort_order',
            'versionTable' => 'articles_tags_versions',
            'foreignKey' => ['article_id', 'tag_id']
        ]);

        $entity = $table->find()->first();
        $this->assertEquals(['sort_order' => 1, 'version_id' => 1, 'version_created' => null], $entity->version(1)->toArray());
        $this->assertEquals(['sort_order' => 2, 'version_id' => 2, 'version_created' => null], $entity->version(2)->toArray());
    }

    /**
     * @return void
     */
    public function testSaveWithCompositeKeys()
    {
        $table = TableRegistry::get('ArticlesTags', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version', [
            'fields' => 'sort_order',
            'versionTable' => 'articles_tags_versions',
            'foreignKey' => ['article_id', 'tag_id']
        ]);

        $entity = $table->find()->first();
        $entity->sort_order = 3;
        $table->save($entity);
        $this->assertEquals(3, $entity->version_id);
        $this->assertEquals(['sort_order' => 3, 'version_id' => 3, 'version_created' => null], $entity->version(3)->toArray());
    }

    /**
     * @return void
     */
    public function testGetAdditionalMetaData()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version', [
            'versionTable' => 'versions_with_user',
            'additionalVersionFields' => ['created', 'user_id'],
        ]);
        $article = $table->find('all')->first();

        $versionTable = TableRegistry::get('Version', ['table' => 'versions_with_user']);

        $results = $table->find('versions')->toArray();

        $this->assertSame(2, $results[0]['_versions'][1]['version_user_id']);
        $this->assertSame(3, $results[0]['_versions'][2]['version_user_id']);
    }

    /**
     * @return void
     */
    public function testAssociations()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');

        $this->assertTrue($table->associations()->has('articleversion'));
        $versions = $table->getAssociation('articleversion');
        $this->assertInstanceOf('Cake\Orm\Association\HasMany', $versions);
        $this->assertEquals('__version', $versions->getProperty());

        $this->assertTrue($table->associations()->has('articlebodyversion'));
        $bodyVersions = $table->getAssociation('articlebodyversion');
        $this->assertInstanceOf('Cake\Orm\Association\HasMany', $bodyVersions);
        $this->assertEquals('body_version', $bodyVersions->getProperty());
    }

    /**
     * @return void
     */
    public function testGetVersionId()
    {
        // init test data
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity',
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->where(['version_id' => 2])->first();
        $article->title = 'First Article Version 3';
        $table->save($article);

        // action in controller receiving outdated data
        $table->patchEntity($article, ['version_id' => 2]);

        $this->assertEquals(2, $article->version_id);
        $this->assertEquals(3, $table->getVersionId($article));
    }
}
