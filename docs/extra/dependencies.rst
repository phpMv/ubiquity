Ubiquity dependencies
=====================
- ``^php 7.4``
- ``phpmv/ubiquity`` => Ubiquity core
In production
-------------
Templating
^^^^^^^^^^
Twig is required if it is used as a template engine, which is not a requirement.

- ``twig/twig`` => Template engine

In development
--------------
Webtools
^^^^^^^^
- ``phpmv/ubiquity-dev`` => dev classes for webtools and devtools since v2.3.0
- ``phpmv/php-mv-ui`` => Front library
- ``mindplay/annotations`` => Annotations library, required for generating models, cache...
- ``monolog/monolog`` => Logging
- ``czproject/git-php`` => Git operations (+ require git console)

Devtools
^^^^^^^^
- ``phpmv/ubiquity-devtools`` => Cli console
- ``phpmv/ubiquity-dev`` => dev classes for webtools and devtools since v2.3.0
- ``mindplay/annotations`` => Annotations library, required for generating models, cache...

Testing
^^^^^^^
- ``codeception/codeception`` => Tests
- ``codeception/c3`` => C3 integration
- ``phpmv/ubiquity-codeception`` => Codeception for Ubiquity

.. |br| raw:: html
   <br />
