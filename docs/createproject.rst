Project creation
=================
After installing :doc:`install`, 
in your terminal, call the *new* command in the root folder of your web server : 

Samples
-------
A simple project

.. code-block:: bash
   
   Ubiquity new projectName
   
A project with UbiquityMyAdmin interface

.. code-block:: bash
   
   Ubiquity new projectName -a
   
A project with bootstrap and semantic-ui themes installed

.. code-block:: bash
   
   Ubiquity new projectName --themes=bootstrap,semantic

Installer arguments
-------------------

+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
| short name | name        | role                            | default                        | Allowed values                | Since devtools |
+============+=============+=================================+================================+===============================+================+
|      b     | dbName      | Sets the database name.         |                                |                               |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      s     | serverName  | Defines the db server address.  |                    `127.0.0.1` |                               |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      p     | port        | Defines the db server port.     |                         `3306` |                               |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      u     | user        | Defines the db server user.     |                         `root` |                               |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      w     | password    | Defines the db server password. |                           `''` |                               |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      h     | themes      | Install themes.                 |                                | semantic,bootstrap,foundation |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      m     | all-models  | Creates all models from db.     |                        `false` |                               |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      a     | admin       | Adds UbiquityMyAdmin interface. |                        `false` |                               |                |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      i     | siteUrl     | Defines the site URL.           |`http://127.0.0.1/{projectname}`|                               | 1.2.6          |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+
|      e     | rewriteBase | Sets the base for rewriting.    |              `/{projectname}/` |                               | 1.2.6          |
+------------+-------------+---------------------------------+--------------------------------+-------------------------------+----------------+

Arguments usage
---------------

short names
^^^^^^^^^^^
Example of creation of the **blog** project, connected to the **blogDb** database, with generation of all models

.. code-block:: bash
   
   Ubiquity new blog -b=blogDb -m=true 

long names
^^^^^^^^^^^
Example of creation of the **blog** project, connected to the **bogDb** database, with generation of all models and integration of semantic theme

.. code-block:: bash
   
   Ubiquity new blog --dbName=blogDb --all-models=true --themes=semantic 

Running
-------

To start the embedded web server and test your pages, run from the application root folder:

.. code-block:: bash
   
   Ubiquity serve
   
The web server is started at ``127.0.0.1:8090``
