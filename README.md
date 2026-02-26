# "One Is Enough" library TYPO3 extension

[![TYPO3 V12](https://img.shields.io/badge/TYPO3-12-orange.svg)](https://get.typo3.org/version/12)
[![TYPO3 V13](https://img.shields.io/badge/TYPO3-13-orange.svg)](https://get.typo3.org/version/13)
[![License](https://img.shields.io/github/license/oliverklee-de/oelib)](https://packagist.org/packages/oliverklee/oelib)
[![GitHub CI Status](https://github.com/oliverklee-de/oelib/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/oliverklee-de/oelib/actions)
[![Coverage Status](https://coveralls.io/repos/github/oliverklee-de/oelib/badge.svg?branch=main)](https://coveralls.io/github/oliverklee-de/oelib?branch=main)

This extension provides useful stuff for extension development: helper functions
for unit testing, templating and
automatic configuration checks.

Most of the documentation is in ReST format
[in the Documentation/ folder](Documentation/) and is rendered
[as part of the TYPO3 documentation](https://docs.typo3.org/typo3cms/extensions/oelib/).

## Running the tests locally

You will need to have a Git clone of the extension for this with the Composer
dependencies installed.

### Running the unit tests

#### On the command line

To run all unit tests on the command line:

```bash
composer check:tests:unit
```

To run all unit tests in a directory or file (using the directory
`Tests/Unit/Model/` as an example):

```bash
.Build/vendor/bin/phpunit -c .Build/vendor/typo3/testing-framework/Resources/Core/Build/UnitTests.xml Tests/Unit/Model/
```

#### In PhpStorm

First, you need to configure the path to PHPUnit in the settings:

Languages & Frameworks > PHP > Test Frameworks

In this section, configure PhpStorm to use the Composer autoload and the script
path `.Build/vendor/autoload.php` within
your project.

In the Run/Debug configurations for PHPUnit, use an alternative configuration
file:

`.Build/vendor/typo3/testing-framework/Resources/Core/Build/UnitTests.xml`
