Anax Database
==================================

[![Latest Stable Version](https://poser.pugx.org/anax/database/v/stable)](https://packagist.org/packages/anax/database)
[![Join the chat at https://gitter.im/mosbth/anax](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/canax?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](https://travis-ci.org/canax/database.svg?branch=master)](https://travis-ci.org/canax/database)
[![CircleCI](https://circleci.com/gh/canax/database.svg?style=svg)](https://circleci.com/gh/canax/database)

[![Build Status](https://scrutinizer-ci.com/g/canax/database/badges/build.png?b=master)](https://scrutinizer-ci.com/g/canax/database/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/canax/database/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/canax/database/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/canax/database/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/canax/database/?branch=master)

[![Maintainability](https://api.codeclimate.com/v1/badges/ab0c4d472565d95e64ff/maintainability)](https://codeclimate.com/github/canax/database/maintainability)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/6dff6044d25646e9bcaea3a333108ded)](https://www.codacy.com/app/mosbth/database?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=canax/database&amp;utm_campaign=Badge_Grade)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d831fd4c-b7c6-4ff0-9a83-102440af8929/mini.png)](https://insight.sensiolabs.com/projects/d831fd4c-b7c6-4ff0-9a83-102440af8929)

Anax Database module for wrapping PHP PDO with an additional layer of utilities, providing support for a SQL query builder and an Active Record implementation.

The module is tested using MySQL and SQLite.

Work is ongoing to move the SQL query builder and the Active Record part to their own repos. The aim is to make this module cleaner and only supporting the layer right above PHP PDO. Therefore is the neither of those documented here.



Table of content
------------------

* [Class, interface, trait](#class-interface-trait)
* [Exceptions](#exceptions)
* [Configuration file](#configuration-file)
* [DI service](#di-service)
* [Access as framework service](#access-as-framework-service)
* [Create a connection](#create-a-connection)
* [Perform a SELECT query](#perform-a-select-query)
* [Perform an INSERT, UPDATE, DELETE query](#perform-an-insert-update-delete-query)
* [Last insert id](#last-insert-id)
* [Row count, affected rows](#row-count-affected-rows)
* [Throw exception on failure](#throw-exception-on-failure)



Class, interface, trait
------------------

The following classes, interfaces and traits exists.

These parts is the foundation for the database module, supporting extra utilities and additional error handling.

| Class, interface, trait            | Description |
|------------------------------------|-------------|
| `Anax\Database\Database`           | Wrapper class for PHP PDO with enhanced error handling and extra utilities. |
| `Anax\Database\DatabaseConfigure`  | An alternative class that can be configured from a Anax configuration file. |

<!--
The following parts are related to the feature of a SQL query builder.

| Class, interface, trait            | Description |
|------------------------------------|-------------|
| `Anax\Database\QueryBuilderTrait`  | A trait implementing SQL query builder, to be used together with `Anax\Database\Database`. |
| `Anax\Database\DatabaseQueryBuilder` | An alternate configurable database class using the SQL query builder trait. |

The following parts are related to the feature of Active Record.

| Class, interface, trait            | Description |
|------------------------------------|-------------|
| `Anax\Database\ActiveRecordModel`  | An Active Record implementation using the SQL query builder. |
-->



Exceptions
------------------

All exceptions are in the namespace `Anax\Database\Exception\`. The following exceptions exists and may be thrown. 

| Exception               | Description |
|-------------------------|-------------|
| `Exception`             | General module specific exception, for example when connection fail. |

<!--
| `BuildException`        | Failing to build an SQL expression, related to the query builder. |
| `ActiveRecordException` | Related to the active record implementation. |
-->



Configuration file
------------------

This is a sample configuration file, it is usually stored in `config/database.php`.

```php
<?php
/**
 * Config file for Database.
 *
 * Example for MySQL.
 *  "dsn" => "mysql:host=localhost;dbname=test;",
 *  "username" => "test",
 *  "password" => "test",
 *  "driver_options"  => [
 *      \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
 *  ],
 *
 * Example for SQLite.
 *  "dsn" => "sqlite:memory::",
 *
 */
return [
    "dsn"             => null,
    "username"        => null,
    "password"        => null,
    "driver_options"  => null,
    "fetch_mode"      => \PDO::FETCH_OBJ,
    "table_prefix"    => null,
    "session_key"     => "Anax\Database",

    // True to be very verbose during development
    "verbose"         => null,

    // True to be verbose on connection failed
    "debug_connect"   => false,
];
```

You can use if-statements within the configuration file to serve different configurations for local development environment, staging and or production environment.



DI service
------------------

The session is created as a framework service within `$di`. The following is a sample on how the session service is created through `config/di/db.php`.

```php
<?php
/**
 * Configuration file for database service.
 */
return [
    // Services to add to the container.
    "services" => [
        "db" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Anax\Database\DatabaseConfigure();
                $obj->configure("database.php");
                return $obj;
            }
        ],
    ],
];
```

1. The object is created.
1. The configuration file is read and applied.

The service is lazy loaded and not created until it is used.



Access as framework service
------------------

You can access the module as a framework service.

```php
$sql = "SELECT * FROM movie;";

# $app style
$app->db->connect();
$res = $app->db->executeFetchAll($sql);

# $di style
$db = $di->get("db");
$db->connect();
$res = $db->executeFetchAll($sql);
```



Create a connection
------------------

You must connect to the database before using it.

You may call `$db->connect()` many times, the connection is however only made once, the first time, so it is safe to call the method several times.

```php
# $app style
$app->db->connect();

# $di style
$di->get("db")->connect();
```



Perform a SELECT query
------------------

You connect and perform the query which returns a resultset.

```php
$sql = "SELECT * FROM movie;";

# $app style
$app->db->connect();
$res = $app->db->executeFetchAll($sql);

# $di style
$db = $di->get("db");
$db->connect();
$res = $db->executeFetchAll($sql);
```

The contents of `$res` is depending on the configuration key which default is set to `"fetch_mode" => \PDO::FETCH_OBJ,`.



Perform an INSERT, UPDATE, DELETE query
------------------

These queries, that updates the database, uses `$db->execute()` and does not return a resultset.

```php
$sql = "UPDATE movie SET title = ? WHERE id = ?;";

# $app style
$app->db->connect();
$app->db->execute($sql, ["Some title", 1]);

# $di style
$db = $di->get("db");
$db->connect();
$db->execute($sql, ["Some title", 1]);
```



Last insert id
------------------

You can check the last inserted id when doing INSERT where the primary key is auto generated.

```php
$sql = "INSERT INTO movie (title) VALUES (?);";

# $app style
$app->db->connect();
$app->db->execute($sql, ["Some title"]);
$id = $app->lastInsertId();

# $di style
$db = $di->get("db");
$db->connect();
$db->execute($sql, ["Some title"]);
$id = $db->lastInsertId();
```



Row count, affected rows
------------------

You can check how many rows that are affected by the last INSERT, UPDATE, DELETE statement.

```php
$sql = "DELETE FROM movie;";

# $app style
$app->db->connect();
$app->db->execute($sql);
$num = $app->rowCount();

# $di style
$db = $di->get("db");
$db->connect();
$db->execute($sql);
$num = $db->rowCount();
```



Throw exception on failure
------------------

Exception are in general thrown as soon as something fails.

The exception is module specific `Anax\Database\Exception\Exception` and contains details from the error message from the PDO layer, either from the statement or from the PDO-object, depending on what type of error happens.



License
------------------

This software carries a MIT license.



```
 .  
..:  Copyright (c) 2013 - 2018 Mikael Roos, mos@dbwebb.se
```
