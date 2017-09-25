Revision history
=================================

v1.1.4 (2017-09-25)
---------------------------------

* Make all private methods protected in ActiveRecordModel to enable subclassing.


v1.1.3 (2017-09-18)
---------------------------------

* Fix DatabaseQueryBuilder::configure now returns self.


v1.1.2 (2017-09-14)
---------------------------------

* DatabaseQueryBuilder::configure now returns self.


v1.1.1 (2017-09-14)
---------------------------------

* DatabaseConfigure::configure now returns self.


v1.1.0 (2017-09-05)
---------------------------------

* Adding Active Record implementation.
* Prepare to work as databasedriven models.
* Adding querybuilder.
* Moving exception into Anax\Database\Exception.
* Creating DatabaseConfigure::configure and removing setDefaultsFromConfiguration, breaking change.


v1.0.8 (2017-05-31)
---------------------------------

* Bug: when throwing statement exception on pdo failure.


v1.0.7 (2017-05-31)
---------------------------------

* Bug: change $sql to $query in Database.


v1.0.5 (2017-05-29)
---------------------------------

* Make statementException protected to work with extends.
* Reengineer fetch methods in Database.


v1.0.4 (2017-04-07)
---------------------------------

* Rewrote Database class and moved debug utilities to DatabaseDebug.
* Cleaned up Database.
* Cleaned up DatabaseTest.


v1.0.3 (2017-03-31)
---------------------------------

* Connect returns self for chaining.


v1.0.2 (2017-03-31)
---------------------------------

* Rename DatabaseConfigurable to DatabaseConfigure.
* Fix error in composer.json.


v1.0.1 (2017-03-31)
---------------------------------

* Add configurable variant of Database as DatabaseConfigurable.


v1.0.0 (2017-03-31)
---------------------------------

* First version to include in anax for test.
* Extracted from mos/cdatabase to be an anax module.
