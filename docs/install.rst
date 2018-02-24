Ubiquity-devtools installation
===========================

Install Composer
----------------
**ubiquity** utilizes Composer to manage its dependencies. So, before using, you will need to make sure you have `Composer <http://getcomposer.org/>`_ installed on your machine.

Install Ubiquity-devtools
----------------------
Download the Ubiquity-devtools installer using Composer.

.. code-block:: bash
   
   composer global require phpmv/ubiquity-devtools 1.0.x-dev

Make sure to place the ``~/.composer/vendor/bin`` directory in your PATH so the **Ubiquity** executable can be located by your system.


Once installed, the simple ``Ubiquity new`` command will create a fresh micro installation in the directory you specify.
For instance, ``Ubiquity new blog`` would create a directory named **blog** containing an Ubiquity project:

.. code-block:: bash
   
   Ubiquity new blog

You can see more options about installation by reading the :doc:`createproject` section.