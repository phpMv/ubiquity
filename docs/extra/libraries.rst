.. _composer:
Composer management
===================
.. |br| raw:: html

   <br />

.. note:: This part is accessible from the **webtools**, so if you created your project with the **-a** option or with the **create-project** command..

Access
------

From the webtools, activate the **composer** part, 

.. image:: /_static/images/composer/composer-elm.png
   :class: bordered

or go directly to ``http://127.0.0.1:8090/Admin/composer``.

Dependencies list
-----------------
The interface displays the list of already installed dependencies, and those that are directly installable.

.. image:: /_static/images/composer/composer-dependencies.png
   :class: bordered
   

Dependency installation
-----------------------
Among the listed dependencies:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on the **add** button of the dependencies you want to add.

.. image:: /_static/images/composer/composer-add-1.png
   :class: bordered

Then click on the **Generate composer update** button:

.. image:: /_static/images/composer/composer-add-2.png
   :class: bordered

The validation generates the update.

For non listed dependencies:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on the **Add dependency** button :

.. image:: /_static/images/composer/composer-add-dependency.png
   :class: bordered

- Enter a vendor name (provider) ;
- Select a package in the list ;
- Select eventually a version (if none, the last stable version will be installed).

Dependency removal
------------------

Click on the **remove** button of the dependencies you want to add.

.. image:: /_static/images/composer/composer-remove-1.png
   :class: bordered

Then click on the **Generate composer update** button, and validate the update.

.. note:: It is possible to perform several addition or deletion operations and validate them simultaneously.

Composer optimization
---------------------

Click on the **Optimize autoloader** button.

This optimize composer autoloading with an authoritative classmap.

