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
use Cake\Event\EventManager;

class VersionBehaviorCustomMetadataTest extends TestCase
{
    public $fixtures = [
        'plugin.Josegonzalez\Version.CustomMetaData\versions',
        'plugin.Josegonzalez\Version.articles',
    ];

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
    }

    public function testSaveWithValidMetaData()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        EventManager::instance()->attach(
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
                                ->hydrate(false)
                                ->toArray();
        $this->assertEquals('foo', $results[0]['custom_field']);

        $article->title = 'Titulo';
        $table->save($article);

        $results = $versionTable->find('all')
                                ->where(['foreign_key' => $article->id])
                                ->hydrate(false)
                                ->toArray();
        $this->assertEquals('bar', $results[4]['custom_field']);
    }

    public function testSaveWithInvalidMetaData()
    {
        $table = TableRegistry::get('Articles', [
            'entityClass' => 'Josegonzalez\Version\Test\TestCase\Model\Behavior\TestEntity'
        ]);
        $table->addBehavior('Josegonzalez/Version.Version');
        $article = $table->find('all')->first();
        EventManager::instance()->attach(
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
                                ->hydrate(false)
                                ->toArray();
        $this->assertEquals('foo', $results[0]['custom_field']);

        $article->title = 'Titulo';
        $table->save($article);

        $results = $versionTable->find('all')
                                ->where(['foreign_key' => $article->id])
                                ->hydrate(false)
                                ->toArray();
        $this->assertNull($results[4]['custom_field']);
    }
}
