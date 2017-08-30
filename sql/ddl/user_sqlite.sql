--
-- Creating a User table and inserting example users.
--



--
-- Table User
--
DROP TABLE IF EXISTS User;
CREATE TABLE User (
    "id" INTEGER PRIMARY KEY NOT NULL,
    "acronym" VARCHAR(80) UNIQUE NOT NULL,
    "password" VARCHAR(255),
    "created" TIMESTAMP,
    "updated" DATETIME,
    "deleted" DATETIME,
    "active" DATETIME
);
