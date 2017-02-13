NamaeSpace
==========

[![Build Status](https://travis-ci.org/gong023/namae-space.svg?branch=master)](https://travis-ci.org/gong023/namae-space)

NamaeSpace is util command for PHP namespace. this command enables you to find and replace namespace using static analysis.

## Installation

```
composer require --dev gong023/namae-space
```

## Usage

### Find

```
namaespace find -C $HOME/your/project \ # path to your project composer.json
                -F Name\\YourClass      # Name what you want to find
```

`namaespace` command stdouts usage of `Name\\YourClass`.

See `--help` to know further.

### Replace

```
namaespace replace -C $HOME/your/project      \ # path to your project composer.json
                   -O Origin\\YourOriginClass \ # Replaced OriginName
                   -N New\\YourNewClass         # NewClassName which you want to replace
```

`namaespace` command finds `YourOriginClass`, and then replaces it to `YourNewClass`.

You can pass `-D` or `--dry_run` option if you wanna test before replace. See `--help` to know further.

Unlike IDE, NamaeSpace can change Global namespace to be named.

## How does it work

`namaespace` finds paths by reading composer.json and analyses codes. 

Mainly analysis is delegated to [nikic/PHP-Parser](https://github.com/nikic/PHP-Parser/). You don't have to worry about instability of regex.

And analysis is executed under multi processing. You can pass `-M` or `--max_process` option to control number of process for any commands.
