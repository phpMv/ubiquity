![php-mv-UI](https://static.kobject.net/ubiquity/images/logo-ubiquity.png "Ubiquity")

[php MVC Ubiquity framework](https://ubiquity.kobject.net), One of the fastest frameworks, with the main features of the bests

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpMv/ubiquity/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpMv/ubiquity/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/phpMv/ubiquity/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phpMv/ubiquity/?branch=master) [![Documentation Status](https://readthedocs.org/projects/micro-framework/badge/?version=latest)](http://micro-framework.readthedocs.io/en/latest/?badge=latest)
 [![SensioLabsInsight](https://insight.sensiolabs.com/projects/17973125-9452-4d32-af68-75ecfc2ff658/mini.png)](https://insight.sensiolabs.com/projects/17973125-9452-4d32-af68-75ecfc2ff658)
 [![Total Downloads](https://poser.pugx.org/phpmv/ubiquity/downloads)](https://packagist.org/packages/phpmv/ubiquity)
 [![Latest Unstable Version](https://poser.pugx.org/phpmv/ubiquity/v/unstable)](https://packagist.org/packages/phpmv/ubiquity)
 [![Latest Stable Version](https://poser.pugx.org/phpmv/ubiquity/v/stable)](https://packagist.org/packages/phpmv/ubiquity)
 [![License](https://poser.pugx.org/phpmv/ubiquity/license)](https://packagist.org/packages/phpmv/ubiquity) [![Join the chat at https://gitter.im/ubiquity-framework/community](https://badges.gitter.im/ubiquity-framework/community.svg)](https://gitter.im/ubiquity-framework/community?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)



# Main features
  - MVC
  - Dependency injection
  - PSR-4 Autoloader
  - Router based on annotations
  - ORM implementing Data Mapper
  - Multi-level cache
  - Rest Server
  - Web admin interface (UbiquityMyAdmin)
  - Scaffolding
  - Console Admin interface (Devtools)
  
# Installation

The easiest way to install **Ubiquity** is to use [devtools](https://github.com/phpMv/ubiquity-devtools)
* Install Ubiquity-devtools:
```bash
composer global require phpmv/ubiquity-devtools
```
* Create a project:
```bash
Ubiquity new firstProject -a -q=semantic
```
* Start the server:
```bash
Ubiquity serve
```
# Need some help?
Ubiquity is a recent project and does not have a community yet.
In the meantime, you can consult:
 - [Quick-start guide](https://micro-framework.readthedocs.io/en/latest/quickstart.html) to discover the framework
 - [Documentation](https://micro-framework.readthedocs.io/en/latest/) to go deeper
 - [API documentation](http://api.kobject.net/ubiquity/) to search further

For further assistance please feel free to : 
 - ask your questions directly using [gitter](https://gitter.im/ubiquity-framework/community)
 - create an [issue](https://github.com/phpMv/ubiquity/issues/new) if you notice a bug or suspicious behavior

# Performances
Ubiquity is fast and efficient, see for yourself [orm-benchmarks](https://orm-benchmarks.kobject.net)

# About design choices
Ubiquity was created in April 2017.

The project tries to simplify the development process, to empower web developers delivering value through their applications.
It aims to reconcile performance and ease of handling.

This dual purpose has led to some design choices:

>Get inspired by best practices and successful concepts from other frameworks, but do not try to reproduce what is not in the logic of PHP.

Some PHP frameworks were inspired by the Java world, which has contributed to more professional php development.
But java is not PHP, environments and languages are completely different (though their syntax are similar), and what is good in Java is not necessarily in PHP.

Ubiquity wants to stay in the spirit of PHP and what it does best, for example:
  - By using php (packed) arrays because they are effective in php (with php7 optimization)
  - By not creating instances of classes to inject for the core part of the framework, to prefer the use of classes with static methods

>Not multiplying the ways of doing things.

if a method or technique is satisfactory, there's no reason to implement an alternative version, especially if there is a risk of degrading performance, or complicating the handling of the framework.

>Avoiding multiple external dependencies, which are sometimes loaded when they are never used.
- They prevent the developer from optimizing his own code.
- In some applications, the dependency loading time is more expensive than running the application code.

The framework used must give the developer the means to optimize his application and not the other way around.

# Preview of some features
## Devtools console program
```bash
Ubiquity help
```
The program in console mode makes it easy to perform all the repetitive tasks related to the design of projects:
- creations : project, controllers, actions, routes, models, views, 
- checking : routes, models, validators
- scaffolding (CRUD + authentification)

## Admin interface
Like the console, the administration interface makes it possible to act on the main components of the framework.

When creating a project, it can be installed with the **-a** and **-q=semantic** options (for Semantic-UI).
```bash
ubiquity new firstProject -a -q=semantic
```
![Admin interface](https://static.kobject.net/ubiquity/images/admin-interface.png "Admin interface")

# Donations
You can tell us your pleasure in using Ubiquity, giving us a star.
Thank you!

