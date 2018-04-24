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
