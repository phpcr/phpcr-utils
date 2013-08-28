# PHPCR Utilities [![Build Status](https://secure.travis-ci.org/phpcr/phpcr-utils.png)](http://travis-ci.org/phpcr/phpcr-utils)

Commands and implementations for common tasks for the PHP Content Repository (PHPCR) API.

If you are using or implementing [PHPCR](https://github.com/phpcr/phpcr) you
probably want some of the things in here.

PHPCR Users: Note that your PHPCR implementation probably already bundles the
utils.

## License

This code is dual licensed under the MIT license and the Apache License Version
2.0. Please see the file LICENSE in this folder.


# Documentation

The utils bring you a couple of *Commands* you can use to interact with a PHPCR
repository on the command line. Additionally we provide a couple of helper
classes for common tasks.

## Commands

There are a couple of useful commands to interact with a PHPCR repository.

To use the console, make sure you have initialized the git submodules of
phpcr-utils, then copy cli-config.php.dist to cli-config.php and adjust it
to your implementation of PHPCR. Then you can run the commands from the
phpcr-utils directory with ``./bin/phpcr``
NOTE: If you are using PHPCR inside of Symfony, the DoctrinePHPCRBundle
provides the commands inside the normal Symfony console and you don't need to
prepare anything special.

To get a list of the available commands, run `bin/phpcr` or set the commands up
in your application. Running `bin/phpcr help <command-name>` outputs the
documentation of that command.

## Helper Classes

The helper classes provide implementations for basic common tasks to help users
and implementers of PHPCR. They are all in the namespace PHPCR\Util

### PathHelper

Used to manipulate paths. Implementations are recommended to use this, and
applications also profit from it. Using `dirname` and similar file system
operations on paths is not compatible with Microsoft Windows systems, thus you
should always use the methods in PathHelper.

### NodeHelper

This helper has some generally useful methods like one to generate empty
`nt:unstructured` nodes to make sure a parent path exists. It also provides
some useful helper methods for implementations.

### UUIDHelper

This little helper is mainly of interest for PHPCR implementers. It generates
valid *Universally Unique IDs* and can determine whether a given string is a
valid UUID.
We recommend all implementations to use this implementation to guarantee
consistent behaviour.

### QOM QueryBuilder

The ``QueryBuilder`` is a fluent query builder with method names matching the
[Doctrine QueryBuilder](http://www.doctrine-project.org/docs/orm/2.1/en/reference/query-builder.html)
on top of the QOM factory. It is the easiest way to programmatically build a
PHPCR query.

### Query Object Model Converter

In the PHPCR\Util\QOM namespace we provide, implementation-independant code to
convert between SQL2 and QOM. ``Sql2ToQomQueryConverter`` parses SQL2 queries
into QOM . ``QomToSql2QueryConverter`` generates SQL2 out of a QOM.

### TraversingItemVisitor

This ``ItemVisitorInterface`` implementation is a basic implementation of crawling
a PHPCR tree. You can extend it to define what it should do while crawling the
tree.
