iLoveIMG Api - Php Library
--------------------------

[![Build Status](https://travis-ci.org/ilovepdf/iloveimg-php.svg?branch=master)](https://travis-ci.org/ilovepdf/iloveimg-php)
[![Latest Stable Version](https://poser.pugx.org/ilovepdf/iloveimg-php/version)](https://packagist.org/packages/ilovepdf/iloveimg-php)
[![Total Downloads](https://poser.pugx.org/ilovepdf/iloveimg-php/downloads.svg)](https://packagist.org/packages/ilovepdf/iloveimg-php)
[![License](https://poser.pugx.org/ilovepdf/iloveimg-php/license)](https://packagist.org/packages/ilovepdf/iloveimg-php)

A library in php for [iLoveIMG Api](https://developer.iloveimg.com)

You can sign up for a iLoveIMG account at https://developer.iloveimg.com

Develop and automate PDF processing tasks like Compress PDF, Merge PDF, Split PDF, convert Office to PDF, PDF to JPG, Images to PDF, add Page Numbers, Rotate PDF, Unlock PDF, stamp a Watermark and Repair PDF. Each one with several settings to get your desired results.

## Requirements

PHP 7.1 and later.

## Install

### Using composer

You can install the library via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require ilovepdf/iloveimg-php
```

To use the library, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

```php
require_once('vendor/autoload.php');
```


### Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/ilovepdf/iloveimg-php/releases). Then, to use the library, include the `init.php` file.

```php
require_once('/path/to/iloveimg-php/init.php');
```

## Getting Started

Simple usage looks like:

```php
$iloveimg = new Iloveimg('project_public_id','project_secret_key');
$myTask = $iloveimg->newTask('compress');
$file1 = $myTask->addFile('file1.jpg');
$myTask->execute();
$myTask->download();
```

## Samples

See samples folder.

## Documentation

Please see https://developer.iloveimg.com/docs for up-to-date documentation.
