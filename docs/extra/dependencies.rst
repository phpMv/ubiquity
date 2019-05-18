Ubiquity dependencies
=====================
- ``^php 7.1``
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
- ``phpmv/php-mv-ui`` => Front library
- ``mindplay/annotations`` => Annotations library, required for generating models, cache...
- ``monolog/monolog`` => Logging
- ``czproject/git-php`` => Git operations (+ require git console)

Devtools
^^^^^^^^
- ``phpmv/ubiquity-devtools`` => Cli console
- ``mindplay/annotations`` => Annotations library, required for generating models, cache...

Testing
^^^^^^^
- ``codeception/codeception`` => Tests
- ``codeception/c3`` => C3 integration
- ``phpmv/ubiquity-codeception`` => Codeception for Ubiquity

.. |br| raw:: html
   <br />
