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
  `content` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

> You may optionally add a `version_id` field of type `integer` to the table which is being versioned. This will store the latest version number of a given page.

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

There are two behavior configurations that may be used:

- `versionTable`: (Default: `version`) The name of the table to be used to store versioned data. It may be useful to use a different table when versioning multiple types of entities.
- `versionField`: (Default: `version_id`) The name of the field in the versioned table that will store the current version. If missing, the plugin will continue to work as normal.
