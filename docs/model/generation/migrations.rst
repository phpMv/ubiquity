Migrations
==========
.. note::
   Ubiquity migrations are not really migrations. Migrations are not done from php code, written or generated. |br|
   They consist in generating the SQL code allowing to update the database from the cache of the existing models.

Updating an existing model
--------------------------
Consider the following model representing a user:

.. image:: /_static/images/model/scratch/user-model.png
   :class: bordered

Let's add a phone field, with the devtools:

.. code-block:: bash

   Ubiquity model


.. image:: /_static/images/model/scratch/add-field-phone.png
   :class: console

Migrations
----------

Starting by displaying the information on possible migrations:

.. code-block:: bash

   Ubiquity info-migrations


.. image:: /_static/images/model/scratch/info-migrations-1.png
   :class: console

We can then proceed to the execution:

.. code-block:: bash

   Ubiquity migrations


.. image:: /_static/images/model/scratch/migrations-1.png
   :class: console


.. |br| raw:: html

   <br />