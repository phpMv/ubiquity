Rest
====

The REST module implements a basic CRUD, |br|
with an authentication system, directly testable in the administration part.

REST and routing
----------------
The router is essential to the REST module, since REST (Respresentation State Transfer) is based on URLs and HTTP methods.

.. note::
   For performance reasons, REST routes are cached independently of other routes. |br|
   It is therefore necessary to start the router in a particular way to activate the REST routes and not to obtain a recurring 404 error.

The router is started in ``services.php``.

Without activation of REST routes:

.. code-block:: php
   :caption: app/config/services.php
   
   ...
   Router::start();

To enable REST routes in an application that also has a non-REST part:

.. code-block:: php
   :caption: app/config/services.php
   
   ...
   Router::startAll();

To activate only Rest routes:

.. code-block:: php
   
   Router::startRest();
   

It is possible to start routing conditionally (this method will only be more efficient if the number of routes is large in either part):

.. code-block:: php
   :caption: app/config/services.php
   
   ...
	if($config['isRest']()){
		Router::startRest();
	}else{
		Router::start();
	}

Resource REST
-------------

A REST controller can be directly associated with a resource (a model).

Creation
++++++++

With devtools:

.. code-block:: bash
   
   Ubiquity rest RestUsersController -r=User -p=/rest/users

Or with webtools:

Go to the **REST** section and choose **Add a new resource**:

.. image:: /_static/images/rest/addNewResource.png
   :class: bordered

The created controller :

.. code-block:: php
   :linenos:
   :caption: app/controllers/RestUsersController.php
   
	namespace controllers;
	
	/**
	 * Rest Controller RestUsersController
	 * @route("/rest/users","inherited"=>true,"automated"=>true)
	 * @rest("resource"=>"models\\User")
	 */
	class RestUsersController extends \Ubiquity\controllers\rest\RestController {
	
	}

Since the attributes **automated** and **inherited** of the route are set to true, the controller has the default routes of the parent class.

.. note
   The base controller RestController is not standardized, it should be considered as an example for data interrogation.

Test interface
++++++++++++++

Webtools provide an interface for querying datas:

.. image:: /_static/images/rest/createdResource.png
   :class: bordered

Getting an instance
~~~~~~~~~~~~~~~~~~~

A user instance can be accessed by its primary key (**id**):

.. image:: /_static/images/rest/getOneResource.png
   :class: bordered

Inclusion of associated members: the organization of the user

.. image:: /_static/images/rest/getOneResourceInclude.png
   :class: bordered

Inclusion of associated members: organization, connections and groups of the user

.. image:: /_static/images/rest/getOneResourceIncludeAll.png
   :class: bordered

Getting multiple instances
~~~~~~~~~~~~~~~~~~~~~~~~~~

Getting all instances:

.. image:: /_static/images/rest/getAllOrgas.png
   :class: bordered

Setting a condition:

.. image:: /_static/images/rest/condition-orgas.png
   :class: bordered

Including associated members:

.. image:: /_static/images/rest/include-orgas.png
   :class: bordered

Adding an instance
~~~~~~~~~~~~~~~~~~

The datas are sent by the **POST** method, with a content type defined at ``application/x-www-form-urlencoded``:

Add name and domain parameters:

.. image:: /_static/images/rest/post-parameters.png
   :class: bordered

The addition requires an authentication, so an error is generated, with the status 401:

.. image:: /_static/images/rest/unauthorized-post.png
   :class: bordered

The administration interface allows you to simulate the default authentication and obtain a token, by requesting the **connect** method:

.. image:: /_static/images/rest/connect.png
   :class: bordered

The token is then automatically sent in the following requests. |br|
The record can then be inserted.

.. image:: /_static/images/rest/added.png
   :class: bordered

Updating an instance
~~~~~~~~~~~~~~~~~~~~

Deleting an instance
~~~~~~~~~~~~~~~~~~~~

Authentification
----------------
Ubiquity REST implements an Oauth2 authentication with Bearer tokens. |br|
Only methods with ``@authorization`` annotation require the authentication. |br|

The **connect** method of a REST controller establishes the connection and returns a new token. |br|
It is up to the developer to override this method to manage a possible authentication with login and password.

.. image:: /_static/images/rest/token.png
   :class: bordered
   
Simulation of a connection with login
+++++++++++++++++++++++++++++++++++++

The connection consists simply in sending a user variable by the post method. |br|
If the user is provided, the ``connect`` method of ``$server`` instance returns a valid token that is stored in session (the session acts as a database here).

.. code-block:: php
   :linenos:
   :emphasize-lines: 18
   :caption: app/controllers/RestOrgas.php
   
	namespace controllers;
	
	use Ubiquity\utils\http\URequest;
	use Ubiquity\utils\http\USession;
	
	/**
	 * Rest Controller RestOrgas
	 * @route("/rest/orgas","inherited"=>true,"automated"=>true)
	 * @rest("resource"=>"models\\Organization")
	 */
	class RestOrgas extends \Ubiquity\controllers\rest\RestController {
		
		public function connect(){
			if(!URequest::isCrossSite()){
				if(URequest::isPost()){
					$user=URequest::post("user");
					if(isset($user)){
						$tokenInfos=$this->server->connect ();
						USession::set($tokenInfos['access_token'], $user);
						$tokenInfos['user']=$user;
						echo $this->_format($tokenInfos);
						return;
					}
				}
			}
			throw new \Exception('Unauthorized',401);
		}
	}

For each request with authentication, it is possible to retrieve the connected user (it is added here in the response headers) :

.. code-block:: php
   :linenos:
   :emphasize-lines: 18-20
   :caption: app/controllers/RestOrgas.php
   
	namespace controllers;
	
	use Ubiquity\utils\http\URequest;
	use Ubiquity\utils\http\USession;
	
	/**
	 * Rest Controller RestOrgas
	 * @route("/rest/orgas","inherited"=>true,"automated"=>true)
	 * @rest("resource"=>"models\\Organization")
	 */
	class RestOrgas extends \Ubiquity\controllers\rest\RestController {
		
		...
		
		public function isValid($action){
			$result=parent::isValid($action);
			if($this->requireAuth($action)){
				$key=$this->server->_getHeaderToken();
				$user=USession::get($key);
				$this->server->_header('active-user',$user,true);
			}
			return $result;
		}
	}

Customizing
-----------

Response
~~~~~~~~

Server
~~~~~~


APIs
----
SimpleRestAPI
+++++++++++++

JsonApi
+++++++

see https://jsonapi.org/

.. |br| raw:: html

   <br />
