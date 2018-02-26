Project creation
=================
After installing :doc:`install`, 
in a bash console, call the *new* command in the root folder of your web server : 

.. code-block:: bash
   
   Ubiquity new projectName

Installer arguments
-------------------

+------------+------------+---------------------------------+-----------+-----------------------+
| short name | name       | role                            | default   | Allowed values        |
+============+============+=================================+===========+=======================+
|      b     | dbName     | Sets the database name.         |           |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      s     | serverName | Defines the db server address.  | 127.0.0.1 |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      p     | port       | Defines the db server port.     |      3306 |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      u     | user       | Defines the db server user.     |      root |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      w     | password   | Defines the db server password. |        '' |                       |
+------------+------------+---------------------------------+-----------+-----------------------+
|      q     | phpmv      | Integrates phpMv-UI toolkit.    |     false | semantic,bootstrap,ui |
+------------+------------+---------------------------------+-----------+-----------------------+
|      m     | all-models | Creates all models from db.     |     false |                       |
+------------+------------+---------------------------------+-----------+-----------------------+

Arguments usage
---------------

short names
^^^^^^^^^^^
Example of creation of the blog project, connected to the blogDb database, with generation of all models

.. code-block:: bash
   
   Ubiquity new blog -b=blogDb -m=true 

long names
^^^^^^^^^^^
Example of creation of the blog project, connected to the bogDb database, with generation of all models and integration of phpMv-toolkit

.. code-block:: bash
   
   Ubiquity new blog --dbName=blogDb --all-models=true --phpmv=semantic 

.. note:: Markdown doesn't support a lot of the features of Sphinx,
          like inline markup and directives. However, it works for
          basic prose content. reStructuredText is the preferred
          format for technical documentation, please read `this blog post`_
          for motivation.
