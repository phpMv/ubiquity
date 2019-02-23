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

.. image:: /_static/images/model/gene-web/update-dbName.png
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
To generate all models, click on **(Re)create all models** button.

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
      
      Ubiquity new quick-start -q=semantic -a  -m -b=messagerie 


