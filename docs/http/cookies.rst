Cookie
======
.. note:: For all Http features, Ubiquity uses technical classes containing static methods. 
          This is a design choice to avoid dependency injection that would degrade performances.

The **UCookie** class provides additional functionality to more easily manipulate native **$_COOKIES** php array.

Cookie creation or modification
-------------------------------

.. inportant:: 
   Cookies are part of the HTTP header, so cookie creation must be called before any output is sent to the browser.

.. code-block:: php

   use Ubiquity\utils\http\UCookie;
      
   $cookie_name = 'user';
   $cookie_value = 'John Doe';
   UCookie::set($cookie_name, $cookie_value);//duration : 1 day

Creating a cookie that lasts 5 days:

.. code-block:: php
   
   UCookie::set($cookie_name, $cookie_value,5*60*60*24);
   
On a particular domain:

.. code-block:: php
   
   UCookie::set($cookie_name, $cookie_value,5*60*60*24,'/admin');
   
Sending a cookie without urlencoding the cookie value:

.. code-block:: php
   
   UCookie::setRaw($cookie_name, $cookie_value);
   
Testing the cookie creation:

.. code-block:: php
   
   if(UCookie::setRaw($cookie_name, $cookie_value)){
   	//cookie created
   }
   
Retrieving a Cookie
-------------------

.. code-block:: php
   
   $userName=UCookie::get('user');
   
Testing the existence
^^^^^^^^^^^^^^^^^^^^^

.. code-block:: php
   
   if(UCookie::exists('user')){
   	//do something if cookie user exists
   }

Using a default value
^^^^^^^^^^^^^^^^^^^^^
If the page cookie does not exist, the default value of 1 is returned:

.. code-block:: php
   
   $page=UCookie::get('page',1);
   
Deleting a cookie
-----------------

Deleting the cookie with the name **page**:

.. code-block:: php
   
   UCookie::delete('page');
   
Deleting all cookies
--------------------
Deleting all cookies from the entire domain:

.. code-block:: php
   
   UCookie::deleteAll();
   
Deleting all cookies from the domain **admin**:

.. code-block:: php
   
   UCookie::deleteAll('/admin');
