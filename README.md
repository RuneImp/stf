STF (Simple Thing Framework)
============================

This is the beginings of a SCM tracking a the PHP framework I've been using for years as a Full Stack Developer. It has existed in many forms on many, many projects. The repo's current state represents the process of normalizing the last useful version of the framework which used PEAR style naming conventions to namespaced so that a [PSR-4][] compliant autoloader could be used.

**Status**: _DO NOT USE!_

I'm not yet done with the normalization and as such the framework is horribly broken currently.

Autoloader
----------

What I believe is a [PSR-4][] compliant autoloader (I've not investigated fully yet) is included in the framework at `RuneImp/STF/ClassLoader.php`.

### Features

* Can handle PEAR style file naming conventions
* Can handle common (old school) `class`, `lib`, `inc` file prefixes and suffixes
* Easily modified to handle class filenames that end in other than `.php`
* Utilizes APC cache (if present) to store lookup info for found class files.
* Can cache lookup info for found classes if the global constant `STF_CACHE_PATH` is defined and points to a writable directory.

ToDo
----

* [ ] Normalize all file and class names
* [ ] Fix all references
* [ ] Save the universe

[PSR-4]: http://www.php-fig.org/psr/psr-4/



