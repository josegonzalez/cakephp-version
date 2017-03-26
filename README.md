[![Build Status](https://img.shields.io/travis/josegonzalez/cakephp-version/master.svg?style=flat-square)](https://travis-ci.org/josegonzalez/cakephp-version)
[![Coverage Status](https://img.shields.io/coveralls/josegonzalez/cakephp-version.svg?style=flat-square)](https://coveralls.io/r/josegonzalez/cakephp-version?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/josegonzalez/cakephp-version.svg?style=flat-square)](https://packagist.org/packages/josegonzalez/cakephp-version)
[![Latest Stable Version](https://img.shields.io/packagist/v/josegonzalez/cakephp-version.svg?style=flat-square)](https://packagist.org/packages/josegonzalez/cakephp-version)
[![Documentation Status](https://readthedocs.org/projects/cakephp-version/badge/?version=latest&style=flat-square)](https://readthedocs.org/projects/cakephp-version/?badge=latest)
[![Gratipay](https://img.shields.io/gratipay/josegonzalez.svg?style=flat-square)](https://gratipay.com/~josegonzalez/)

# Version

A CakePHP 3.x plugin that facilitates versioned database entities

## Installation

Add the following lines to your application's `composer.json`:

```json
"require": {
    "josegonzalez/cakephp-version": "dev-master"
}
```

followed by the command:

`composer update`

Or run the following command directly without changing your `composer.json`:

`composer require josegonzalez/cakephp-version:dev-master`

## Usage

In your app's `config/bootstrap.php` add:

```php
Plugin::load('Josegonzalez/Version', ['bootstrap' => true]);
```

## Usage

Run the following schema migration:

```sql
CREATE TABLE `version` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `version_id` int(11) DEFAULT NULL,
    `model` varchar(255) NOT NULL,
    `foreign_key` int(10) NOT NULL,
    `field` varchar(255) NOT NULL,
    `content` text NULL,
    `created` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

> Note that the `content` field must be nullable if you want to be able to version any nullable fields in your application.

> You may optionally add a `version_id` field of type `integer` to the table which is being versioned. This will store the latest version number of a given page.

If you wish to create the table using `cakephp/migrations` then you will need to use a migration that looks something like this:

```php
<?php

use Phinx\Migration\AbstractMigration;

class CreateVersions extends AbstractMigration
{
    public function change()
    {
        $this->table('version')
             ->addColumn('version_id', 'integer', ['null' => true])
             ->addColumn('model', 'string')
             ->addColumn('foreign_key', 'integer')
             ->addColumn('field', 'string')
             ->addColumn('content', 'text', ['null' => true])
             ->addColumn('created', 'datetime')
             ->create();
    }
}
```

Add the following line to your entities:

```php
use \Josegonzalez\Version\Model\Behavior\Version\VersionTrait;
```

And then include the trait in the entity class:

```php
class PostEntity extends Entity {
    use VersionTrait;
}
```

Attach the behavior in the models you want with:

```php
public function initialize(array $config) {
    $this->addBehavior('Josegonzalez/Version.Version');
}
```

Whenever an entity is persisted - whether via insert or update - that entity is also persisted to the `version` table. You can access a given revision by executing the following code:

```php
// Will contain a generic `Entity` populated with data from the specified version.
$version = $entity->version(1);
```

You can optionally retrieve all the versions:

```php
$versions = $entity->versions();
```

### Storing Additional Meta Data

`cakephp-version` dispatches an event `Model.Version.beforeSave` which you can optionally handle to attach additional meta-data about the version.

Add the necessary additional fields to your migration, for example:

```sql
CREATE TABLE `version` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `version_id` int(11) DEFAULT NULL,
    `model` varchar(255) NOT NULL,
    `foreign_key` int(10) NOT NULL,
    `field` varchar(255) NOT NULL,
    `content` text,
    `created` datetime NOT NULL,
    `custom_field1` varchar(255) NOT NULL, /* column to store our metadata */
    `custom_field2` varchar(255) NOT NULL, /* column to store our metadata */
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Then define an event listener to handle the event and pass in additional metadata, for example:

```php
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

class VersionListener implements EventListenerInterface {

    public function implementedEvents() {
        return array(
            'Model.Version.beforeSave' => 'insertAdditionalData',
        );
    }

    public function insertAdditionalData(Event $event) {
        return [
            'custom_field1' => 'foo',
            'custom_field2' => 'bar'
        ];
    }
}
```

Your event listener can then be attached in your project, for example:

```php
use App\Event\VersionListener;
use Cake\Event\EventManager;

$VersionListener = new VersionListener();
EventManager::instance()->attach($VersionListener);
```

Note that handling this event also allows you to modify/overwrite values generated by the plugin.
This can provide useful functionality, but ensure that if your event listener returns array keys called
`version_id`, `model`, `foreign_key`, `field`, `content` or `created` that this is the intended behavior.

#### Storing user_id as Meta Data
To store the `user_id` as additional meta data is easiest in combination with [Muffin/Footprint](https://github.com/UseMuffin/Footprint).
The above `insertAdditionalData()` method could then look like this:

```php
    /**
     * @param \Cake\Event\Event $event
     *
     * @return array
     */
    public function insertAdditionalData(Event $event) 
    {
        $data = [
            ...
        ];

        if ($event->data('_footprint')) {
            $user = $event->data('_footprint');
            $data += [
                'user_id' => $user['id'],
            ];
        }

        return $data;
    }
```
Any controller with the `FootprintAwareTrait` used will then provide the `_footprint` data into the model layer for this event callback to use.

### Bake Integration

If you load the plugin using `'bootstrap' => true`, this plugin can be used to autodetect usage via the properly named database table. To do so, simply create a table with the `version` schema above named after the table you'd like to revision plus the suffix `_versions`. For instance, to version the following table:

```sql
CREATE TABLE `posts` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `category_id` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `status` varchar(255) NOT NULL DEFAULT 'published',
    `visibility` varchar(255) NOT NULL DEFAULT 'public',
    `title` varchar(255) NOT NULL DEFAULT '',
    `route` varchar(255) DEFAULT NULL,
    `content` text,
    `published_date` datetime DEFAULT NULL,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Create the following table:

```sql
CREATE TABLE `posts_versions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `version_id` int(11) NOT NULL,
    `model` varchar(255) NOT NULL,
    `foreign_key` int(11) NOT NULL,
    `field` varchar(255) NOT NULL,
    `content` text,
    `created` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

You can create a migration for this with the following bake command:

```shell
bin/cake bake migration create_posts_versions version_id:integer model foreign_key:integer field content:text created
```

> You'll also want to set the `content` field in this migration to nullable, otherwise you won't be able to version fields that can be nulled.

To track the current version in the `posts` table, you can create a migration to add the `version_id` field to the table:

```shell
bin/cake bake migration add_version_id_to_posts version_id:integer
```

### Configuration

There are three behavior configurations that may be used:

- `versionTable`: (Default: `version`) The name of the table to be used to store versioned data. It may be useful to use a different table when versioning multiple types of entities.
- `versionField`: (Default: `version_id`) The name of the field in the versioned table that will store the current version. If missing, the plugin will continue to work as normal.
- `additionalVersionFields`: (Default `['created']`) The additional or custom fields of the versioned table to be exposed as well. By default prefixed with `version_`, e.g. `'version_user_id'` for `'user_id'`.
- `referenceName`: (Default: db table name) Discriminator used to identify records in the version table.
- `onlyDirty`: (Default: false) Set to true to version only dirty properties.
