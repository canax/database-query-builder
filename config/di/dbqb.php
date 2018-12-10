<?php
/**
 * Configuration file for database query builder service.
 */
return [
    // Services to add to the container.
    "services" => [
        "dbqb" => [
            "shared" => true,
            "callback" => function () {
                $db = new \Anax\DatabaseQueryBuilder\DatabaseQueryBuilder();

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
