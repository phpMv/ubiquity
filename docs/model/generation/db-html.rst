.. _db-html:
Generate models from Database with webtools
===========================================

.. note::
   In this part, your project is already created. |br|
   If you do not have a mysql database on hand, you can download this one: :download:`messagerie.sql </model/messagerie.sql>`

Configuration
-------------

Check the database configuration with **webtools** at ``http://127.0.0.1:8090/Admin/``:

Go to **models** part:

.. image:: /_static/images/model/gene-web/models-part.png
   :class: bordered
   
.. note::
   The configuration file is located in **app/config/config.php**
  
Change the configuration of the database to use the **messagerie** database:

- Click on the **Edit config file** button
- Select the **database** part

.. image:: /_static/images/model/gene-web/update-config-db.png
   :class: bordered
   
- Enter **messagerie** in the **dbName** field
- Click on Test button to check the connection
- Validate with the **Save configuration** button

.. image:: /_static/images/model/gene-web/models-part-2.png
   :class: bordered
   
Generation
----------
To generate all models, click on **(Re-)create all models** button.

.. image:: /_static/images/model/gene-web/models-generated.png
   :class: bordered

Generate the models cache by clicking on **Re-init all models cache** button:

.. image:: /_static/images/model/gene-web/cache-generated.png
   :class: bordered

That's all! |br|
The models are generated and operational. |br|   
You can now see datas.

.. note::
   It is possible to generate models automatically when creating a project with the ``-m`` option for models and ``-b`` to specify the database:
   
   .. code-block:: bash
      
      Ubiquity new quick-start -a  -m -b=messagerie 

Navigation between data
-----------------------

You can now see the data and move between related objects:

.. image:: /_static/images/model/gene-web/datas-navigation.png
   :class: bordered
   
You can also perform basic operations on objects:

- Create
- Read (navigate and see)
- Update
- Delete
- Search

Checking
--------

Class diagram
^^^^^^^^^^^^^
Still in the **models** part, click on the **models generation** step:

.. image:: /_static/images/model/gene-web/step-models-generation.png

Then click on **Classes diagram** button:

.. image:: /_static/images/model/gene-web/access-class-diagram.png
   :class: bordered
   

You can see the class diagram, using the `yUML API <https://yuml.me/>`_

.. image:: /_static/images/model/gene-web/classes-diagram.png
   :class: bordered

Models meta-datas
^^^^^^^^^^^^^^^^^
To see the metadatas of a created model:

Select a model, and activate the **Structure** tab:

.. image:: /_static/images/model/gene-web/structure-tab.png
   :class: bordered
   
You can also view the partial class diagram:

.. image:: /_static/images/model/gene-web/settings-class-diagram.png
   :class: bordered

Models validation
^^^^^^^^^^^^^^^^^
The third tab gives information about object validation:

.. image:: /_static/images/model/gene-web/validation-info.png
   :class: bordered

.. note::
   The validation rules were generated automatically with the classes. |br|
   They are defined through ``@validator`` annotations on each member of a class and are stored in cache.

The **Validate instances** button is used to check the validity of the instances:

.. image:: /_static/images/model/gene-web/validation-results.png
   :class: bordered

.. note::
   It is normal that by default all instances in the database do not check all validation rules.

Generated classes
^^^^^^^^^^^^^^^^^
Generated classes are located in **app/models** folder, if the configuration of `mvcNS.models` has not been changed.

.. note::
   If you want to know more about:
   
   - object/relational mapping rules, see the :doc:`ORM part</model/models>`
   - data querying and persistence, see :doc:`DAO part</model/dao>`

The **Settings** class:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/models/Settings.php

         namespace models;
         class Settings{

            #[Id]
            #[Column(name: 'id', nullable: false, dbType: 'int(11)')]
            #[Validator('id', constraints: ['autoinc'=>true])]
            private $id;

            #[Column(name: 'name', nullable: true, dbType: 'varchar(45)')]
            #[Validator('length', constraints: ['max'=>45])]
            private $name;

            #[OneToMany(mappedBy: 'settings', className: 'models\\Organizationsettings')]
            private $organizationsettingss;
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/models/Settings.php

         namespace models;
         class Settings{
            /**
             * @id
             * @column("name"=>"id","nullable"=>false,"dbType"=>"int(11)")
             * @validator("id","constraints"=>array("autoinc"=>true))
             */
            private $id;

            /**
             * @column("name"=>"name","nullable"=>true,"dbType"=>"varchar(45)")
             * @validator("length","constraints"=>array("max"=>45))
             */
            private $name;

            /**
             * @oneToMany("mappedBy"=>"settings","className"=>"models\\Organizationsettings")
             */
            private $organizationsettingss;
         }

.. important::

   Any modification on the classes (code or annotations) requires the reset of the cache to be taken into account.
   
   .. code-block:: bash
   
      Ubiquity init-cache -t=models


.. |br| raw:: html

   <br />