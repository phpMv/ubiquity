.. _rest:
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

A REST controller can be directly associated with a model.

.. note::
   If you do not have a mysql database on hand, you can download this one: :download:`messagerie.sql </model/messagerie.sql>`

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

Add name and domain parameters by clicking on the **parameters** button:

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
The update follows the same scheme as the insertion.

Deleting an instance
~~~~~~~~~~~~~~~~~~~~

.. image:: /_static/images/rest/delete-instance.png
   :class: bordered

Authentification
----------------
Ubiquity REST implements an Oauth2 authentication with Bearer tokens. |br|
Only methods with ``@authorization`` annotation require the authentication, these are the modification methods (add, update & delete). |br|

.. code-block:: php
   :emphasize-lines: 7
   
		/**
		 * Update an instance of $model selected by the primary key $keyValues
		 * Require members values in $_POST array
		 * Requires an authorization with access token
		 *
		 * @param array $keyValues
		 * @authorization
		 * @route("methods"=>["patch"])
		 */
		public function update(...$keyValues) {
			$this->_update ( ...$keyValues );
		}

The **connect** method of a REST controller establishes the connection and returns a new token. |br|
It is up to the developer to override this method to manage a possible authentication with login and password.

.. image:: /_static/images/rest/token.png
   :class: bordered
   
Simulation of a connection with login
+++++++++++++++++++++++++++++++++++++

In this example, the connection consists simply in sending a user variable by the post method. |br|
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
		
		/**
		 * This method simulate a connection.
		 * Send a <b>user</b> variable with <b>POST</b> method to retreive a valid access token
		 * @route("methods"=>["post"])
		 */
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

Use the webtools interface to test the connection:

.. image:: /_static/images/rest/connected-user.png
   :class: bordered
   

Customizing
-----------
Api tokens
++++++++++

It is possible to customize the token generation, by overriding the ``getRestServer`` method:

.. code-block:: php
   :linenos:
   :caption: app/controllers/RestOrgas.php
   
	namespace controllers;
	
	use Ubiquity\controllers\rest\RestServer;
	class RestOrgas extends \Ubiquity\controllers\rest\RestController {
		
		...
		
		protected function getRestServer(): RestServer {
			$srv= new RestServer($this->config);
			$srv->setTokenLength(32);
			$srv->setTokenDuration(4800);
			return $srv;
		}
	}

Allowed origins
+++++++++++++++
Allowed origins allow to define the clients that can access the resource in case of a cross domain request by defining The **Access-Control-Allow-Origin** response header.

.. code-block:: php
   :linenos:
   :caption: app/controllers/RestOrgas.php
   
	class RestOrgas extends \Ubiquity\controllers\rest\RestController {
		
		...
		
		protected function getRestServer(): RestServer {
			$srv= new RestServer($this->config);
			$srv->setAllowOrigin('http://mydomain/');
			return $srv;
		}
	}

It is possible to authorize several origins:

.. code-block:: php
   :linenos:
   :caption: app/controllers/RestOrgas.php
   
	class RestOrgas extends \Ubiquity\controllers\rest\RestController {
		
		...
		
		protected function getRestServer(): RestServer {
			$srv= new RestServer($this->config);
			$srv->setAllowOrigins(['http://mydomain1/','http://mydomain2/']);
			return $srv;
		}
	}

Response
++++++++

To change the response format, it is necessary to create a class inheriting from ``ResponseFormatter``. |br|
We will take inspiration from **HAL**, and change the format of the responses by:

- adding a link to self for each resource
- adding an ``_embedded`` attribute for collections 
- removing the ``data`` attribute for unique resources

.. code-block:: php
   :linenos:
   :caption: app/controllers/RestOrgas.php
   
	namespace controllers\rest;
	
	use Ubiquity\controllers\rest\ResponseFormatter;
	use Ubiquity\orm\OrmUtils;
	
	class MyResponseFormatter extends ResponseFormatter {
		
		public function cleanRestObject($o, &$classname = null) {
			$pk = OrmUtils::getFirstKeyValue ( $o );
			$r=parent::cleanRestObject($o);
			$r["links"]=["self"=>"/rest/orgas/get/".$pk];
			return $r;
		}
		
		public function getOne($datas) {
			return $this->format ( $this->cleanRestObject ( $datas ) );
		}
		
		public function get($datas, $pages = null) {
			$datas = $this->getDatas ( $datas );
			return $this->format ( [ "_embedded" => $datas,"count" => \sizeof ( $datas ) ] );
		}
	}

Then assign ``MyResponseFormatter`` to the REST controller by overriding the ``getResponseFormatter`` method:

.. code-block:: php
   :linenos:
   :caption: app/controllers/RestOrgas.php
   
	class RestOrgas extends \Ubiquity\controllers\rest\RestController {
		
		...
		
		protected function getResponseFormatter(): ResponseFormatter {
			return new MyResponseFormatter();
		}
	}

Test the results with the getOne and get methods:

.. image:: /_static/images/rest/getOneFormatted.png
   :class: bordered



.. image:: /_static/images/rest/getFormatted.png
   :class: bordered
   

APIs
----
Unlike REST resources, APIs controllers are multi-resources.

SimpleRestAPI
+++++++++++++

JsonApi
+++++++
Ubiquity implements the jsonApi specification with the class ``JsonApiRestController``. |br|
JsonApi is used by  `EmberJS <https://api.emberjs.com/ember-data/release/classes/DS.JSONAPIAdapter>`_ and others. |br|
see https://jsonapi.org/ for more.

Creation
~~~~~~~~

With devtools:

.. code-block:: bash
   
   Ubiquity restapi JsonApiTest -p=/jsonapi

Or with webtools:

Go to the **REST** section and choose **Add a new resource**:

.. image:: /_static/images/rest/jsonapi-creation.png
   :class: bordered

Test the api in webtools:

.. image:: /_static/images/rest/jsonapi-admin.png
   :class: bordered
   
Links
~~~~~

The **links** route (index method) returns the list of available urls:

.. image:: /_static/images/rest/jsonapi-links.png
   :class: bordered

Getting an array of objects
~~~~~~~~~~~~~~~~~~~~~~~~~~~
By default, all associated members are included:

.. image:: /_static/images/rest/jsonapi/getAll.png
   :class: bordered

Including associated members
^^^^^^^^^^^^^^^^^^^^^^^^^^^^
you need to use the **include** parameter of the request:

+-------------------------------------------------------+-----------------------------------------------------------+
| URL                                                   | Description                                               |
+=======================================================+===========================================================+
| ``/jsonapi/user?include=false``                       | No associated members are included                        |
+-------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?include=organization``                | Include the organization                                  |
+-------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?include=organization,connections``    | Include the organization and the connections              |
+-------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?include=groupes.organization``        | Include the groups and their organization                 |
+-------------------------------------------------------+-----------------------------------------------------------+


Filtering instances
^^^^^^^^^^^^^^^^^^^
you need to use the **filter** parameter of the request, |br|
**filter** parameter corresponds to the **where** part of an SQL statement:

+--------------------------------------------------------------+-----------------------------------------------------------+
| URL                                                          | Description                                               |
+==============================================================+===========================================================+
| ``/jsonapi/user?1=1``                                        | No filtering                                              |
+--------------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?firstname='Benjamin'``                       | Returns all users named Benjamin                          |
+--------------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?filter=firstname like 'B*'``                 | Returns all users whose first name begins with a B        |
+--------------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?filter=suspended=0 and lastname like 'ca*'`` | Returns all suspended users whose lastname begins with ca |
+--------------------------------------------------------------+-----------------------------------------------------------+


Pagination
^^^^^^^^^^
you need to use the **page[number]** and **page[size]** parameters of the request:

+----------------------------------------------------------+-----------------------------------------------------------+
| URL                                                      | Description                                               |
+==========================================================+===========================================================+
| ``/jsonapi/user``                                        | No pagination                                             |
+----------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?page[number]=1``                         | Display the first page (page size is 1)                   |
+----------------------------------------------------------+-----------------------------------------------------------+
| ``/jsonapi/user?page[number]=1&&page[size]=10``          | Display the first page (page size is 10)                  |
+----------------------------------------------------------+-----------------------------------------------------------+

Adding an instance
~~~~~~~~~~~~~~~~~~

The datas, contained in ``data[attributes]``, are sent by the **POST** method, with a content type defined at ``application/json; charset=utf-8``. |br|

Add your parameters by clicking on the **parameters** button:

.. image:: /_static/images/rest/jsonapi/add-parameters.png
   :class: bordered

The addition requires an authentication, so an error is generated, with the status 401 if the token is absent or expired.

.. image:: /_static/images/rest/jsonapi/add-response.png
   :class: bordered

Deleting an instance
~~~~~~~~~~~~~~~~~~~~
Deletion requires the **DELETE** method, and the use of the **id** of the object to be deleted:

.. image:: /_static/images/rest/jsonapi/delete-response.png
   :class: bordered

.. |br| raw:: html

   <br />
