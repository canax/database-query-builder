Anax Database Query Builder
==================================

[![Latest Stable Version](https://poser.pugx.org/anax/database-query-builder/v/stable)](https://packagist.org/packages/anax/database-query-builder)
[![Join the chat at https://gitter.im/canax/database-query-builder](https://badges.gitter.im/canax/database-query-builder.svg)](https://gitter.im/canax/database-query-builder?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](https://travis-ci.org/canax/database-query-builder.svg?branch=master)](https://travis-ci.org/canax/database-query-builder)
[![CircleCI](https://circleci.com/gh/canax/database-query-builder.svg?style=svg)](https://circleci.com/gh/canax/database-query-builder)

[![Build Status](https://scrutinizer-ci.com/g/canax/database-query-builder/badges/build.png?b=master)](https://scrutinizer-ci.com/g/canax/database-query-builder/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/canax/database-query-builder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/canax/database-query-builder/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/canax/database-query-builder/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/canax/database-query-builder/?branch=master)

[![Maintainability](https://api.codeclimate.com/v1/badges/ab0c4d472565d95e64ff/maintainability)](https://codeclimate.com/github/canax/database-query-builder/maintainability)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/6dff6044d25646e9bcaea3a333108ded)](https://www.codacy.com/app/mosbth/database-query-builder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=canax/database-query-builder&amp;utm_campaign=Badge_Grade)

Anax Database Query Builder module as an extension to [`anax/database`](https://github.com/canax/database) to enable querying the datase using methods instead of SQL.

This module is used to implement the module Database Active Record [`anax\database-active-record`](https://github.com/canax/database-active-record).

The module is tested using MySQL and SQLite.



Table of content
------------------

* [Install](#Install)
* [Development](#Development)
* [Class, interface, trait](#class-interface-trait)
* [Exceptions](#exceptions)
* [DI service](#di-service)
* [Access as framework service](#access-as-framework-service)
* [Basic usage](#Basic-usage)
* [Dependency](#Dependency)
* [License](#License)



Install
------------------

You can install the module from [`anax/database-query-builder` on Packagist](https://packagist.org/packages/anax/database-query-builder) using composer.

```text
composer require anax/database-query-builder
```

You can then copy the default configuration files as a start.

```text
# In the root of your Anax installation
rsync -av vendor/anax/database-query-builder/config .
```



Development
------------------

To work as a developer you clone the repo and install the local environment through make. Then you can run the unit tests.

```text
make install
make test
```



Class, interface, trait
------------------

The following classes, interfaces and traits exists.

The following parts are related to the feature of a SQL query builder.

| Class, interface, trait            | Description |
|------------------------------------|-------------|
| `Anax\Database\QueryBuilderTrait`  | A trait implementing SQL query builder, based upon `Anax\Database\Database`. |
| `Anax\Database\DatabaseQueryBuilder` | A database class using the SQL query builder trait (used by the Active Record module) and extending the database class. |



Exceptions
------------------

All exceptions are in the namespace `Anax\DatabaseQueryBuilder\Exception\`. The following exceptions exists and may be thrown. 

| Exception               | Description |
|-------------------------|-------------|
| `BuildException`        | When failing to build a SQL query. |



DI service
------------------

The database query builder is created as a framework service within `$di`. The following is a sample on how the database query builder service is created through `config/di/dbqb.php`.

```php
/**
 * Configuration file for database query builder service.
 */
return [
    // Services to add to the container.
    "services" => [
        "dbqb" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Anax\DatabaseQueryBuilder\DatabaseQueryBuilder();

                // Load the configuration files
                $cfg = $this->get("configuration");
                $config = $cfg->load("database");

                // Set the database configuration
                $connection = $config["config"] ?? [];
                $db->setOptions($connection);
                $db->setDefaultsFromConfiguration();

                return $db;
            }
        ],
    ],
];
```



Access as framework service
------------------

You can access the module as a framework service and use it as an ordinary database service.

```php
$sql = "SELECT * FROM movie;";

$db = $di->get("dbqb");
$db->connect();
$res = $db->executeFetchAll($sql);
```

This is since the class `\Anax\DatabaseQueryBuilder\DatabaseQueryBuilder` extends the database class `\Anax\Database\Database`.



Basic usage
------------------

This is the basic usage of the query builder.

You start by creating a database object from the query builder class and connect to the database.

```php
$this->db = new DatabaseQueryBuilder([
    "dsn" => "sqlite::memory:",
]);
$this->db->setDefaultsFromConfiguration();
$this->db->connect();
```

This is more or less the same as retrieving the class from the $di container.

You can now create a table.

```php
// Create a table
$this->db->createTable(
    'user',
    [
        'id'    => ['integer', 'primary key', 'not null'],
        'age'   => ['integer'],
        'name'  => ['varchar(10)']
    ]
)->execute();
```

The table is created.

You can now insert rows into the table.

```php
$this->db->insert(
    "user",
    [
        "age" => 3,
        "name" => "three",
    ]
)->execute();

$last = $this->db->lastInsertId(); // 1
$rows = $this->db->rowCount();     // 1
```

You can now query the table.

```php
$res = $this->db->select("*")
                ->from("user")
                ->where("id = 1")
                ->execute()
                ->fetch();

$res->id;   // 1
$res->age;  // 3
$res->name; // "three"
```

That is the basic usage and the idea is to create the SQL-queries using class methods and build tha actual SQL query behind the scene.



<!--
TODO
------------------

Document the whole usecase using update, delete and more selects
getSQL
Execute standard SQL, just through the database class or through some method?

-->



Dependency
------------------

This module depends upon, and extends, the database abstraction layer [`anax\database`](https://github.com/canax/database).

The module is usually used within an Anax installation but can also be used without Anax.



License
------------------

This software carries a MIT license. See [LICENSE.txt](LICENSE.txt) for details.



```
 .  
..:  Copyright (c) 2013 - 2018 Mikael Roos, mos@dbwebb.se
```
