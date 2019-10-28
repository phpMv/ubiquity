![img](https://github.com/richardbmx/ubiquity/blob/master/Banner/banner.png?raw=true)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpMv/ubiquity/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpMv/ubiquity/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/phpMv/ubiquity/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phpMv/ubiquity/?branch=master) [![Documentation Status](https://readthedocs.org/projects/micro-framework/badge/?version=latest)](http://micro-framework.readthedocs.io/en/latest/?badge=latest)
 [![Total Downloads](https://poser.pugx.org/phpmv/ubiquity/downloads)](https://packagist.org/packages/phpmv/ubiquity)
 [![Latest Unstable Version](https://poser.pugx.org/phpmv/ubiquity/v/unstable)](https://packagist.org/packages/phpmv/ubiquity)
 [![Latest Stable Version](https://poser.pugx.org/phpmv/ubiquity/v/stable)](https://packagist.org/packages/phpmv/ubiquity)
 [![License](https://poser.pugx.org/phpmv/ubiquity/license)](https://packagist.org/packages/phpmv/ubiquity) [![Join the chat at https://gitter.im/ubiquity-framework/community](https://badges.gitter.im/ubiquity-framework/community.svg)](https://gitter.im/ubiquity-framework/community?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[Ubiquity](https://ubiquity.kobject.net), one of the fastest frameworks, with the main features of the bests

# Main features
  - MVC
  - Dependency injection
  - PSR-4 Autoloader
  - Router based on annotations
  - ORM implementing Data Mapper
  - Multi-databases & multi-database types support (PDO Mysql, Mysqli, Swoole coroutine Mysql, Tarantool)
  - Multi-level cache
  - Rest Server
  - Web admin interface (UbiquityMyAdmin)
  - Scaffolding
  - Console Admin interface (Devtools)
  - Assets & themes management (since 2.1.0, on a [proposal](https://github.com/phpMv/ubiquity/issues/11) from [@Gildonei](https://github.com/gildonei))
  - Managed servers: fpm/fastcgi with Apache or nginX, [Workerman](https://github.com/walkor/Workerman), [Swoole](https://github.com/swoole/swoole-src), [PHP-PM](https://github.com/php-pm/php-pm) with ReactPHP (Dev only)

# Upgrade
If Ubiquity devtools are already globally installed, and you want to upgrade to the lastest stable version:
```bash
composer global update
```
# Installation

The easiest way to install **Ubiquity** is to use [devtools](https://github.com/phpMv/ubiquity-devtools)
* Install Ubiquity-devtools:
```bash
composer global require phpmv/ubiquity-devtools
```
* Create a project:
```bash
Ubiquity new firstProject -a
```
* Start the server:
```bash
Ubiquity serve
```
# Need some help?
Ubiquity is a recent project and does not yet have a community.
In the meantime, you can consult:
 - [Quick-start guide](https://micro-framework.readthedocs.io/en/latest/quickstart/quickstart.html) to discover the framework
 - [Documentation](https://micro-framework.readthedocs.io/en/latest/) to go deeper
 - [API documentation](http://api.kobject.net/ubiquity/) to search further

For further assistance please feel free to : 
 - ask your questions directly using [gitter](https://gitter.im/ubiquity-framework/community)
 - create an [issue](https://github.com/phpMv/ubiquity/issues/new) if you notice a bug or suspicious behavior

# Performances
Ubiquity executes its own benchmarks, especially for the ORM part:

This test involves loading from a Mysql database:
- 2100 instances of the **Host** class
  - each host is associated with 1 **user**, who can have configured some **virtualhosts**
  - each host has multiple **servers**, of a certain **type**.
  
Approximately 6000 objects are loaded, in this intuitive line with Ubiquity:
```php
$hosts=DAO::getAll(Host::class,"",["user.virtualhosts","servers.stype"]);
```
In regards to this type of related object loading that can be very time consuming with an ORM, Ubiquity is twice as fast as Doctrine based solutions,and more efficient than the others tested.

For equity reasons, all ORMs are using an Eager loading approach.

Unlike the others,Ubiquity:
- does not perform any SQL joins
- executes only 90 queries, against 870 for Doctrine (3190 if the eager loading is not used).

![ORM benchmarks](https://static.kobject.net/ubiquity/images/orm-benchmarks-4.png "ORM benchmarks")

These excellent results have been confirmed by an independent benchmark site : [phpbenchmarks.com](http://www.phpbenchmarks.com/en/comparator/framework)

# About design choices
Ubiquity was created in April 2017.

The project tries to simplify the development process, and empowers web developers who delivering value through their applications.
It aims to combine performance and ease of handling.

This dual purpose has led to some design choices:

>Get inspired by the best practices and successful concepts from other frameworks, but do not try to reproduce things that are not a part of the logic of PHP.

Some PHP frameworks were inspired by the Java world, which has contributed to more professional php development.
But java is not PHP : the environments and languages are completely different (though their syntax is similar). What is good in Java is not necessarily in PHP.

Ubiquity wants to keep the essence of PHP and what it does best, for example:
  - By using php (packed) arrays because they are effective in php (with php7 optimization)
  - By not creating instances of classes to inject for the core part of the framework, to prefer the use of classes with static methods

In this perspective, Ubiquity chooses not to respect certain standards:
For example, by not creating a Response object implementing an interface (see [PSR-7 HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) ) in response to an Http request.

>Not multiplying the ways of doing things.

If a method or technique is optimal, there's no reason to implement an alternative version, especially if there is a risk of degrading the performance or complicating the handling of the framework.

>Avoiding multiple external dependencies, which are sometimes loaded when they are never used.
- They prevent the developer from optimizing his own code.
- In some applications, the dependency loading time is more expensive than running the application code.

The framework used must give the developer the means to optimize his application and not the other way around.

# Preview of some features
## Devtools console program
```bash
Ubiquity help
```
The console mode makes it easy to perform all the repetitive tasks related to the design of projects:
- creations : project, controllers, actions, routes, models, views, 
- checking : routes, models, validators
- scaffolding (CRUD + authentification)

## Scaffolding

Generation of the CRUD elements for a model class with devtools :

```bash
Ubiquity crud --resource=Developers --path=/devs/
```

The generated route **/devs/** provides an entry point for CRUD operations:

![crud index](https://static.kobject.net/ubiquity/images/github/crud-index.png "crud index")

## Admin interface
Like the console, the administration interface makes it possible to act on the main components of the framework.

When creating a project, it can be installed with the **-a** option.
```bash
ubiquity new firstProject -a
```
![Admin interface](https://github.com/phpmv/ubiquity-webtools/blob/master/.github/images/webtools-interface.png "Admin interface")

# Graphic design
- Ubiquity logos and banner: [@richardbmx](https://github.com/richardbmx)

# Donations
You can tell us your pleasure in using Ubiquity, giving us a star,
and you can do even better by [contributing](https://github.com/phpMv/ubiquity/blob/master/CONTRIBUTING.md)...

Thank you!

