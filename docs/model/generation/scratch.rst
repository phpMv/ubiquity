Models creation from scratch
============================
.. note::
   It is often preferable to design a database conceptually and then generate the models from the existing database. |br|
   The creation of models from scratch is only suitable for simple cases, and does not allow to skip a conceptualization phase.

Creating a model
----------------
Consider the following model representing a user:

.. image:: /_static/images/model/scratch/user-model.png
   :class: bordered

We will create it with devtools, in command prompt:

.. code-block:: bash

   Ubiquity model user

.. image:: /_static/images/model/scratch/create-model.png
   :class: console

.. note::
   A primary key is automatically added at creation as an auto-increment. |br|
   It is possible to change the default name of the primary key when launching the command :

   .. code-block:: bash

      Ubiquity model user -k=uid


Adding fields
^^^^^^^^^^^^^
Select the ``Add fields`` menu item:

  - Enter the field names separated by a comma:

.. image:: /_static/images/model/scratch/add-fields.png
   :class: console

  - Enter the field types (db types) in the same way.
  - Provide the list of nullable fields.

.. image:: /_static/images/model/scratch/field-types.png
   :class: console

The added fields:

.. image:: /_static/images/model/scratch/fields-added.png
   :class: console

Generating the class
^^^^^^^^^^^^^^^^^^^^

.. image:: /_static/images/model/scratch/generate-class.png
   :class: console

Below is the created model, without the accessors:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/models/User.php

         namespace models;

         use Ubiquity\attributes\items\Table;
         use Ubiquity\attributes\items\Id;

         #[Table('user')]
         class User{

            #[Id]
            #[Column(name: "id",dbType: "int(11)")]
            #[Validator(type: "id",constraints: ["autoinc"=>true])]
            private $id;

            #[Column(name: "firstname",dbType: "varchar(30)")]
            #[Validator(type: "length",constraints: ["max"=>30,"notNull"=>false])]
            private $firstname;

            #[Column(name: "lastname",dbType: "varchar(45)")]
            #[Validator(type: "length",constraints: ["max"=>45,"notNull"=>false])]
            private $lastname;

            #[Column(name: "email",dbType: "varchar(150)")]
            #[Validator(type: "email",constraints: ["notNull"=>true])]
            #[Validator(type: "length",constraints: ["max"=>150])]
            private $email;
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/models/User.php

         namespace models;

         /**
          * @table("name"=>"user")
          */
         class User{
            /**
             * @id
             * @column("id","int(11)")
             * @validator("id",["autoinc"=>true])
             */
            private $id;

            /**
             * @column("firstname","varchar(30)")
             * @validator("length",["max"=>30,"notNull"=>false])
             */
            private $firstname;

            /**
             * @column("lastname","varchar(45)")
             * @validator("length",["max"=>45,"notNull"=>false])
             */
            private $lastname;

            /**
             * @column("firstname","varchar(150)")
             * @validator("email",["notNull"=>false])
             * @validator("length",["max"=>150])
             */
            private $email;
         }


Modifying existing models
-------------------------

.. code-block:: bash

   Ubiquity model

Without parameters, if some models exist, the ``model`` command suggests their loading:

.. image:: /_static/images/model/scratch/reload.png
   :class: console

The model to achieve is now the following:

.. image:: /_static/images/model/scratch/group_users.png
   :class: bordered

Select the ``Add/switch to model`` menu option, and enter ``group``

.. image:: /_static/images/model/scratch/switch-to-group.png
   :class: console

Add:
  - primary key ``id`` in autoinc
  - the ``name`` field
  - The ``manyToMany`` relation with the ``User`` class :

.. image:: /_static/images/model/scratch/manytomany-users.png
   :class: console

.. |br| raw:: html

   <br />