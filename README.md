NamaeSpace
==========

NamaeSpace is util command for PHP namespace. For now, you can replace namespace by static analysis.

## Installation

```
composer global require gong023/namae-space:dev-master
```

Please make sure you have `~/.composer/vendor/bin` in your `PATH`.

NOTE: Alternatively you can install this command in your repository and will be able to skip input of comoser.json path.

```
composer require --dev gong023/namae-space
```

## Usage

### Replace

You can replace namespace by just inputing this.

```
namaespace replace
```

NamaeSpace will ask every required options interactively.

If you want to skip interactive mode, pass option directly. See `--help` to know more detail.

```
$ namaespace replace --help
  Usage:
    replace [options]
  
  Options:
    -C, --composer_json[=COMPOSER_JSON]
    -A, --additional_path[=ADDITIONAL_PATH]
    -O, --origin_namespace[=ORIGIN_NAMESPACE]
    -N, --new_namespace[=NEW_NAMESPACE]
    -R, --replace_dir[=REPLACE_DIR]
    -D, --dry_run
    -h, --help                                 Display this help message
    -q, --quiet                                Do not output any message
    -V, --version                              Display this application version
        --ansi                                 Force ANSI output
        --no-ansi                              Disable ANSI output
    -n, --no-interaction                       Do not ask any interactive question
    -v|vv|vvv, --verbose                       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
  
  Help:
   replace namespace
```

Unlike IDE, NamaeSpace can change Global namespace to be named.

### Find

WIP

## How does it work

NamaeSpace finds paths by reading composer.json and analysis codes. 

Mainly analyzing is delegated to https://github.com/nikic/PHP-Parser/. So you don't have to worry about instability of regex.

## Information

- Do not forget testing by yourself. NamaeSpace is still beta.
- Multi processing analyzing and `namaespace find` are coming soon.  
- Pull requests and reporting issues are welcome.
