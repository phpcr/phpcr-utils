Changelog
=========

1.2.7
-----

* **2015-07-13**: Added Symfony 3 compatibility for the console commands. If you use
  the commands, update your `cli-config.php` according to `cli-config.php.dist` to set
  the question helper if it is available.

1.2.0
-----

* **2014-10-24**: Fixed SQL2 handling, notably precedency when generating SQL2 and parsing of literals.
* **2014-10-05**: Added PathHelper::getLocalNodeName
* **2014-09-01**: Added PathHelper::relativizePath

1.1.1
-----

* **2014-06-11**: handle escaping fulltext search literal when converting from/to QOM/SQL2

1.1.0
-----

Cleanups and adjustments, particularly on the command handling.

1.1.0-RC1
---------

* **2014-01-08**: Lots of bugfixes and cleanups. Improved CLI commands. If you
  are using the cli-config.php, compare your file with cli-config.php.dist.

* 2013-12-28**: PathHelper::getNodeName validates the path and throws an
  exception if it is not given a valid path with slashes in it.

1.0.0
-----

* **2013-06-15**: [Command] Added `--apply-closure` option to `phpcr:nodes:update` command.
