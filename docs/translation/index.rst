Translation module
==================

.. note::
   The Translation module uses the static class **TranslatorManager** to manage translations.

Module structure
----------------
Translations are grouped by **domain**, within a **locale** :

In the translation root directory (default **app/translations**):

- Each locale corresponds to a subfolder.
- For each locale, in a subfolder, a domain corresponds to a php file.

.. code-block:: bash
   
	translations
	     ├ en_EN
	     │     ├ messages.php
	     │     └ blog.php
	     └ fr_FR
	           ├ messages.php
	           └ blog.php

- each domain file contains an associative array of translations **key-> translation value**
- Each key can be associated with 
   - a translation
   - a translation containing variables (between **%** and **%**)
   - an array of translations for handle pluralization
   
   
.. code-block:: php
   :caption: app/translations/en_EN/messages.php
         
   return [
   	'okayBtn'=>'Okay',
   	'cancelBtn'=>'Cancel',
   	'deleteMessage'=>['No message to delete!','1 message to delete.','%count% messages to delete.']
   ];

Starting the module
-------------------

Module startup is logically done in the **services.php** file. |br|

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   :emphasize-lines: 2
      
   Ubiquity\cache\CacheManager::startProd($config);
   Ubiquity\translation\TranslatorManager::start();

With no parameters, the call of the **start** method uses the locale **en_EN**, without fallbacklocale.

.. important::
   The translations module must be started after the cache has started.
   
Setting the locale
^^^^^^^^^^^^^^^^^^
Changing the locale when the manager starts:

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   :emphasize-lines: 2
      
   Ubiquity\cache\CacheManager::startProd($config);
   Ubiquity\translation\TranslatorManager::start('fr_FR');

Changing the locale after loading the manager:

.. code-block:: php
      
   TranslatorManager::setLocale('fr_FR');

Setting the fallbackLocale
^^^^^^^^^^^^^^^^^^^^^^^^^^

The **en_EN** locale will be used if **fr_FR** is not found:

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   :emphasize-lines: 2
      
   Ubiquity\cache\CacheManager::startProd($config);
   Ubiquity\translation\TranslatorManager::start('fr_FR','en_EN');   

Defining the root translations dir
----------------------------------

If the **rootDir** parameter is missing, the default directory used is ``app/translations``.

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   :emphasize-lines: 2
      
   Ubiquity\cache\CacheManager::startProd($config);
   Ubiquity\translation\TranslatorManager::start('fr_FR','en_EN','myTranslations');

Make a translation
------------------

With php
^^^^^^^^
Translation of the **okayBtn** key into the default locale (specified when starting the manager):

.. code-block:: php
      
   $okBtnCaption=TranslatorManager::trans('okayBtn');

With no parameters, the call of the **trans** method uses the default locale, the domain **messages**.
   
Translation of the **message** key using a variable:

.. code-block:: php
      
   $okBtnCaption=TranslatorManager::trans('message',['user'=>$user]);

In this case, the translation file must contain a reference to the **user** variable for the key **message**:

.. code-block:: php
  :caption: app/translations/en_EN/messages.php    
    
  ['message'=>'Hello %user%!',...];

In twig views:
^^^^^^^^^^^^^^

Translation of the **okayBtn** key into the default locale (specified when starting the manager):

.. code-block:: html+twig
   
   {{ t('okayBtn') }}

Translation of the **message** key using a variable:

.. code-block:: html+twig
      
   {{ t('message',parameters) }}


.. |br| raw:: html

   <br />
