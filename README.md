# PHPCR Utilities [![Build Status](https://secure.travis-ci.org/phpcr/phpcr-utils.png)](http://travis-ci.org/phpcr/phpcr-utils)

Commands and implementations for common tasks for the PHP Content Repository (PHPCR) API.

If you are using or implementing (PHPCR)[https://github.com/phpcr/phpcr] you
probably want some of the things in here.

PHPCR Users: Note that your PHPCR implementation probably already bundles the
utils. Jackalope for example depends on them and they are found in its
lib/phpcr-utils folder.


# Documentation

The utils bring you a couple of *Commands* you can use to interact with a PHPCR
repository on the command line. Additionally we provide a couple of helper
classes for common tasks.

## Commands

There are a couple of useful commands to interact with a phpcr repository.

To use the console, make sure you have initialized the git submodules of
phpcr-utils, then copy cli-config.php.dist to cli-config.php and adjust it
to your implementation of PHPCR. Then you can run the commands from the
phpcr-utils directory with ``./bin/phpcr``
NOTE: If you are using PHPCR inside of Symfony, the DoctrinePHPCRBundle
provides the commands inside the normal Symfony console and you don't need to
prepare anything special.

* ``phpcr:workspace:create <name>``: Create the workspace name in the configured repository
* ``phpcr:register-node-types --allow-update [cnd-file]``: Register namespaces and node types from a "Compact Node Type Definition" .cnd file
* ``phpcr:dump [--sys_nodes[="..."]] [--props[="..."]] [path]``: Show the node names
     under the specified path. If you set sys_nodes=yes you will also see system nodes.
     If you set props=yes you will additionally see all properties of the dumped nodes.
* ``phpcr:purge``: Remove all content from the configured repository in the
     configured workspace
* ``phpcr:sql2``: Run a query in the JCR SQL2 language against the repository and dump
     the resulting rows to the console.

**TODO:**

* Implement commands for phpcr:import and phpcr:export to import and export the
    PHPCR document view and system view XML dumps.
* Implement a simple .cnd parser in PHP and use it to make register-node-types
    work with all repositories


## Helper Classes

The helper classes provide implementations for basic common tasks to help users
and implementors of PHPCR. They are all in the namespace PHPCR\Util


### TraversingItemVisitor

This ``ItemVisitorInterface`` implementation is a basic implementation of crawling
a PHPCR tree. You can extend it to define what it should do while crawling the
tree.


### QOM QueryBuilder

The ``QueryBuilder`` is a fluent query builder with method names matching the
(Doctrine QueryBuilder)[http://www.doctrine-project.org/docs/orm/2.1/en/reference/query-builder.html]
on top of the QOM factory. It is the easiest way to programmatically build a
PHPCR query.


### Query Object Model Converter

In the PHPCR\Util\QOM namespace we provide, implementation-independant code to
convert between SQL2 and QOM. ``Sql2ToQomQueryConverter`` parses SQL2 queries
into QOM . ``QomToSql2QueryConverter`` generates SQL2 out of a QOM.


### UUIDHelper

This little helper is mainly of interest for PHPCR implementors. It generates
valid *Universally Unique IDs* and can determine wheter a given string is a
valid UUID.
We recommend all implementations to use this implementation to guarantee
constistent behaviour.


# TODO

Move tests about the query converter from phpcr-api-tests tests/06_Query/QOM to
the tests folder. How to do the tests without a QOM factory implementation?
