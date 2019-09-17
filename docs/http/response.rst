Response
========
.. note:: For all Http features, Ubiquity uses technical classes containing static methods. 
          This is a design choice to avoid dependency injection that would degrade performances.
          
          The **UResponse** class handles only the headers, not the response body, which is conventionally provided by the content displayed by the calls used to output data (echo, print ...).

The **UResponse** class provides additional functionality to more easily manipulate response headers.

Adding or modifying headers
---------------------------

.. code-block:: php
   
   use Ubiquity\utils\http\UResponse;
   $animal='camel';
   UResponse::header('Animal',$animal);
   
Forcing multiple header of the same type:

.. code-block:: php
   
   UResponse::header('Animal','monkey',false);

Forces the HTTP response code to the specified value:

.. code-block:: php
   
   UResponse::header('Messages',$message,false,500);

   
Defining specific headers
-------------------------
content-type
^^^^^^^^^^^^
Setting the response content-type to **application/json**:

.. code-block:: php
   
   UResponse::asJSON();

Setting the response content-type to **text/html**:

.. code-block:: php
   
   UResponse::asHtml();

Setting the response content-type to **plain/text**:

.. code-block:: php
   
   UResponse::asText();

Setting the response content-type to **application/xml**:

.. code-block:: php
   
   UResponse::asXml();
   
Defining specific encoding (default value is always **utf-8**):

.. code-block:: php
   
   UResponse::asHtml('iso-8859-1');

Cache
-----
Forcing the disabling of the browser cache:

.. code-block:: php
   
   UResponse::noCache();


Accept
------
Define which content types, expressed as MIME types, the client is able to understand. |br|
See `Accept default values <https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation/List_of_default_Accept_values>`_

.. code-block:: php
   
   UResponse::setAccept('text/html');
   

CORS responses headers
----------------------

Cross-Origin Resource Sharing (CORS) is a mechanism that uses additional HTTP headers to tell a browser to let your web application running at one origin (domain) have permission to access selected resources from a server at a different origin. 

Access-Control-Allow-Origin
^^^^^^^^^^^^^^^^^^^^^^^^^^^
Setting allowed origin:

.. code-block:: php
   
   UResponse::setAccessControlOrigin('http://myDomain/');

Access-Control-Allow-methods
^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Defining allowed methods:

.. code-block:: php
   
   UResponse::setAccessControlMethods('GET, POST, PUT, DELETE, PATCH, OPTIONS');
   
Access-Control-Allow-headers
^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Defining allowed headers:

.. code-block:: php
   
   UResponse::setAccessControlHeaders('X-Requested-With, Content-Type, Accept, Origin, Authorization');
   
Global CORS activation
^^^^^^^^^^^^^^^^^^^^^^

enabling CORS for a domain with default values:

- allowed methods:  ``GET, POST, PUT, DELETE, PATCH, OPTIONS``
- allowed headers: ``X-Requested-With, Content-Type, Accept, Origin, Authorization``

.. code-block:: php
   
   UResponse::enableCors('http://myDomain/');

   
Testing response headers
------------------------

Checking if headers have been sent:

.. code-block:: php
   
   if(!UResponse::isSent()){
   	//do something if headers are not send
   }

Testing if response content-type is **application/json**:

.. important:: This method only works if you used the UResponse class to set the headers.
          

.. code-block:: php
   
   if(UResponse::isJSON()){
   	//do something if response is a JSON response
   }

.. |br| raw:: html

   <br />
