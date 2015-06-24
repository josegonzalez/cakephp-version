<?php
namespace Josegonzalez\Version\Test\TestCase\Model\Behavior;

use Cake\Collection\Collection;
use Cake\Event\Event;
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
    public $fixtures = [
        'plugin.Josegonzalez\Version.versions',
        'plugin.Josegonzalez\Version.articles',
    ];

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
    }

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
                                ->hydrate(false)
                                ->toArray();
        $this->assertCount(8, $results);

        $article->title = 'Titulo';
        $table->save($article);

        $versionTable = TableRegistry::get('Version');
        $results = $versionTable->find('all')
                                ->where(['foreign_key' => $article->id])
                                ->hydrate(false)
                                ->toArray();

        $this->assertEquals(3, $article->version_id);
        $this->assertCount(12, $results);
    }

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
                                ->hydrate(false)
                                ->toArray();

        $this->assertCount(1, $results);
        $this->assertEquals('title', $results[0]['field']);
    }

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
}
