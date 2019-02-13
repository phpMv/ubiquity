Quick start guide
=================

Install Composer
----------------
**ubiquity** utilizes Composer to manage its dependencies. So, before using, you will need to make sure you have `Composer <http://getcomposer.org/>`_ installed on your machine.

Install Ubiquity-devtools
-------------------------
Download the Ubiquity-devtools installer using Composer.

.. code-block:: bash
   
   composer global require phpmv/ubiquity-devtools
   
Project creation
----------------
Create the **quick-start** projet with UbiquityMyAdmin interface and Semantic-UI integration

.. code-block:: bash
   
   Ubiquity new quick-start -q=semantic -a
   
Start-up
--------
Start the build-in php server:

.. code-block:: bash
   
   Ubiquity serve
   
Check the correct operation at the address **http://127.0.0.1:8090**:

.. image:: _static/quick-start/quick-start-main.png