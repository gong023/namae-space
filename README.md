NamaeSpace
==========

[![Build Status](https://travis-ci.org/gong023/namae-space.svg?branch=master)](https://travis-ci.org/gong023/namae-space)

NamaeSpace is util command for PHP namespace. For now, you can replace namespace by static analysis.

## Usage

### Replace

You can replace php namespace.

```
namaespace replace -C /Users/wanna_be_170/Documents/mercari-api \ # path to your project composer.json
                   -O Origin\\YourOriginClass \                   # Replaced OriginName
                   -N New\\YourNewClass                           # NewClassName which you want to replace
```

namaespace command will read composer.json, find `YourOriginClass`, then replace it to `YourNewClass`.

Unlike IDE, NamaeSpace can change Global namespace to be named.

You can pass `--dry_run` option if you wanna test before replace. See `--help` to know further.

### Find

WIP

## Installation

## Phar

You can download phar from here.

https://github.com/gong023/namae-space/raw/gh-pages/namaespace.phar

Please do not forget to put namaespace.phar to your $PATH like this:

```
mv namaespace.phar /usr/local/bin/namaespace
```

### Incude your project

Alternatively you can install this command in your project.

In this case, you will be able to skip input of comoser.json path.

```
composer require --dev gong023/namae-space
```

## How does it work

NamaeSpace finds paths by reading composer.json and analysis codes. 

Mainly analyzing is delegated to https://github.com/nikic/PHP-Parser/. You don't have to worry about instability of regex.

## Information

- Do not forget testing by yourself. NamaeSpace is still beta.
- Pull requests and reporting issues are welcome.
