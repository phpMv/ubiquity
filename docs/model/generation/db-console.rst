.. _db-console:
Generate models from Database with devtools
===========================================
.. note::
   In this part, your project is already created. |br|
   If you do not have a mysql database on hand, you can download this one: :download:`messagerie.sql </model/messagerie.sql>`

Configuration
-------------

Check the database configuration with **devtools** console program:

.. code-block:: bash
   
   Ubiquity config database

.. image:: /_static/images/model/check-config.png
   :class: console
   
.. note::
   The configuration file is located in **app/config/config.php**
  
Change the configuration of the database to use the **messagerie** database:

.. code-block:: bash
   
   Ubiquity config:set --database.dbName=messagerie

.. image:: /_static/images/model/update-dbName.png
   :class: console

Generation
----------
To generate all models, use the **all-models** command:

.. code-block:: bash
   
   Ubiquity all-models

.. image:: /_static/images/model/db-created.png
   :class: console

That's all! |br|
The models are generated and operational.

.. note::
   It is possible to generate models automatically when creating a project with the ``-m`` option for models and ``-b`` to specify the database:
   
   .. code-block:: bash
      
      Ubiquity new quick-start -a  -m -b=messagerie 

Checking
--------

Models meta-datas
^^^^^^^^^^^^^^^^^
To obtain the metadatas of all created models:

.. code-block:: bash
   
   Ubiquity info:models

For a precise model:

.. code-block:: bash
   
   Ubiquity info:models -m=Groupe

.. image:: /_static/images/model/model-metas.png
   :class: console

Models validation info
^^^^^^^^^^^^^^^^^^^^^^
To obtain the validation rules for the model **User**:

.. code-block:: bash
   
   Ubiquity info:validation -m=User

.. image:: /_static/images/model/infos-validation-user.png
   :class: console

On a particular member (email):

.. code-block:: bash
   
   Ubiquity info:validation -m=User -f=email

.. image:: /_static/images/model/infos-validation-user-email.png
   :class: console
   
Generated classes
^^^^^^^^^^^^^^^^^
Generated classes are located in **app/models** folder, if the configuration of `mvcNS.models` has not been changed.

.. note::
   If you want to know more about:
   
   - object/relational mapping rules, see the :doc:`ORM part</model/models>`
   - data querying and persistence, see :doc:`DAO part</model/dao>`

The **User** class:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/models/User.php

         namespace models;

         use Ubiquity\attributes\items\Id;
         use Ubiquity\attributes\items\Column;
         use Ubiquity\attributes\items\Validator;
         use Ubiquity\attributes\items\ManyToOne;
         use Ubiquity\attributes\items\JoinColumn;
         use Ubiquity\attributes\items\OneToMany;
         use Ubiquity\attributes\items\ManyToMany;
         use Ubiquity\attributes\items\JoinTable;

         class User{

            #[Id]
            #[Column(name: 'id', nullable: false, dbType: 'int(11)')]
            #[Validator('id', constraints: ['autoinc'=>true])]
            private $id;

            #[Column(name: 'firstname, nullable: false, dbType: 'varchar(65)')]
            #[Validator('length', constraints: ['max'=>65, 'notNull'=>true])]
            private $firstname;

            #[Column(name: 'lastname, nullable: false, dbType: 'varchar(65)')]
            #[Validator('length', constraints: ['max'=>65, 'notNull'=>true])]
            private $lastname;

            #[Column(name: 'email', nullable: false, dbType: 'varchar(255)')]
            #[Validator('email', constraints: ['notNull'=>true])]
            #[Validator('length', constraints: ['max'=>255])]
            private $email;

            #[Column(name: 'password', nullable: true, dbType: 'varchar(255)')]
            #[Validator('length', constraints: ['max'=>255])]
            private $password;

            #[Column(name: 'suspended', nullable: true, dbType: 'tinyint(1)')]
            #[Validator('isBool')]
            private $suspended;

            #[ManyToOne]
            #[JoinColumn(className: 'models\\Organization', name: 'idOrganization', nullable: false)]
            private $organization;

            #[OneToMany(mappedBy: 'user', className: "models\\Connection")]
            private $connections;

            #[ManyToMany(targetEntity: 'models\\Groupe', inversedBy: 'users')]
            #[JoinTable(name: 'groupeusers')]
            private $groupes;
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/models/User.php

         namespace models;

         class User{
            /**
             * @id
             * @column("name"=>"id","nullable"=>false,"dbType"=>"int(11)")
             * @validator("id","constraints"=>array("autoinc"=>true))
             */
            private $id;

            /**
             * @column("name"=>"firstname","nullable"=>false,"dbType"=>"varchar(65)")
             * @validator("length","constraints"=>array("max"=>65,"notNull"=>true))
             */
            private $firstname;

            /**
             * @column("name"=>"lastname","nullable"=>false,"dbType"=>"varchar(65)")
             * @validator("length","constraints"=>array("max"=>65,"notNull"=>true))
             */
            private $lastname;

            /**
             * @column("name"=>"email","nullable"=>false,"dbType"=>"varchar(255)")
             * @validator("email","constraints"=>array("notNull"=>true))
             * @validator("length","constraints"=>array("max"=>255))
             */
            private $email;

            /**
             * @column("name"=>"password","nullable"=>true,"dbType"=>"varchar(255)")
             * @validator("length","constraints"=>array("max"=>255))
             */
            private $password;

            /**
             * @column("name"=>"suspended","nullable"=>true,"dbType"=>"tinyint(1)")
             * @validator("isBool")
             */
            private $suspended;

            /**
             * @manyToOne
             * @joinColumn("className"=>"models\\Organization","name"=>"idOrganization","nullable"=>false)
             */
            private $organization;

            /**
             * @oneToMany("mappedBy"=>"user","className"=>"models\\Connection")
             */
            private $connections;

            /**
             * @manyToMany("targetEntity"=>"models\\Groupe","inversedBy"=>"users")
             * @joinTable("name"=>"groupeusers")
             */
            private $groupes;
         }

.. important::

   Any modification on the classes (code or annotations) requires the reset of the cache to be taken into account.
   
   .. code-block:: bash
   
      Ubiquity init-cache -t=models

Querying
--------

Classes are generated, and models cache also. |br|
At this point, we can already query the database in console mode, to give an idea of the possibilities of the :doc:`DAO part</model/dao>`:

Classic queries
^^^^^^^^^^^^^^^

Getting all the groups:

.. code-block:: bash
   
   Ubiquity dao getAll -r=Groupe

.. image:: /_static/images/model/get-all.png
   :class: console
   
With there organization:

.. code-block:: bash
   
   Ubiquity dao getAll -r=Groupe -i=organization

.. image:: /_static/images/model/get-all-groupes-orga.png
   :class: console

A more complete query: |br|
Search for groups with the word **"list"** in their email, displaying the name, email and organization of each group:

.. code-block:: bash
   
   Ubiquity dao getAll -r=Groupe -c="email like '%liste%'" -f=email,name,organization -i=organization

.. image:: /_static/images/model/query-groupes-orga.png
   :class: console
   
Getting one **User** by id:

.. code-block:: bash

   Ubiquity dao getOne -r=User -c="id=4"
   
.. image:: /_static/images/model/get-one-user.png
   :class: console

uQueries
^^^^^^^^

**UQueries** are special in that they allow to set criteria on the values of the members of the associated objects:


Search for groups with a user named **Shermans**

.. code-block:: bash

   Ubiquity dao uGetAll -r=Groupe -c="users.lastname='Shermans'" -i=users
   
.. image:: /_static/images/model/groupes-sherman.png
   :class: console

We can verify that **Shermans** belongs to the group **Auditeurs**

.. code-block:: bash

   Ubiquity dao uGetAll -r=User -c="groupes.name='Auditeurs' and lastname='Shermans'" -i=groupes
   
.. image:: /_static/images/model/shermans-groupe.png
   :class: console

The same with a parameterized query:

.. code-block:: bash

   Ubiquity dao uGetAll -r=User -c="groupes.name= ? and lastname= ?" -i=groupes -p=Auditeurs,Shermans

.. |br| raw:: html

   <br />