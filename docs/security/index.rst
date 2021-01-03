.. _security:
Security
********

.. |br| raw:: html

   <br />

Guiding principles
==================
Forms validation
----------------
Client-side validation
^^^^^^^^^^^^^^^^^^^^^^
It is preferable to perform an initial client-side validation to avoid submitting invalid data to the server.

Example of the creation of a form in the action of a controller (this part could be located in a dedicated service for a better separation of layers):

.. code-block:: php
   :linenos:
   :caption: app/controllers/UsersManagement.php

    public function index(){
        $frm=$this->jquery->semantic()->dataForm('frm-user',new User());
        $frm->setFields(['login','password','connection']);
        $frm->fieldAsInput('login',
            ['rules'=>'empty']
        );
        $frm->fieldAsInput('password',
            [
                'inputType'=>'password',
                'rules'=>['empty','minLength[6]']
            ]
        );
        $frm->setValidationParams(['on'=>'blur','inline'=>true]);
        $frm->fieldAsSubmit('connection','fluid green','/submit','#response');
        $this->jquery->renderDefaultView();
    }

The Associated View:

.. code-block:: html+twig
   :caption: app/views/UsersManagement/index.html

    {{ q['frm-user'] | raw }}
    {{ script_foot | raw }}
    <div id="response"></div>

.. image:: /_static/images/security/bases/frm-user.png
   :class: bordered

.. note:: The CRUD controllers automatically integrate this client-side validation using the Validators attached to the members of the models.

.. code-block:: php

   #[Column(name: "password",nullable: true,dbType: "varchar(255)")]
   #[Validator(type: "length",constraints: ["max"=>20,"min"=>6])]
   #[Transformer(name: "password")]
   private $password;

Server-side validation
^^^^^^^^^^^^^^^^^^^^^^
It is preferable to restrict the URLs allowed to modify data. |br|
Beforehand, by specifying the Http method in the routes, and by testing the request :

.. code-block:: php

   #[Post(path: "/submit")]
   public function submitUser(){
      if(!URequest::isCrossSite() && URequest::isAjax()){
         $datas=URequest::getPost();//post with htmlEntities
         //Do something with $datas
      }
   }

.. note:: The **Ubiquity-security** module offers additional control to avoid cross-site requests.

After modifying an object, it is possible to check its validity, given the validators attached to the members of the associated Model:

.. code-block:: php

   #[Post(path: "/submit")]
   public function submitUser(){
      if(!URequest::isCrossSite()){
         $datas=URequest::getPost();//post with htmlEntities
         $user=new User();
         URequest::setValuesToObject($user,$datas);

         $violations=ValidatorsManager::validate($user);
         if(\count($violations)==0){
            //do something with this valid user
         } else {
            //Display violations...
         }
      }
   }


DAO operations
--------------
It is always recommended to use parameterized queries, regardless of the operations performed on the data:
   * To avoid SQL injections.
   * To allow the use of prepared queries, speeding up processing.

.. code-block:: php

   $googleUsers=DAO::getAll(User::class,'email like ?',false,['%@gmail.com']);

.. code-block:: php

   $countActiveUsers=DAO::count(User::class,'active= ?',[true]);

.. note:: DAO operations that take objects as parameters use this mechanism by default.

.. code-block:: php

   DAO::save($user);

.. tips:: It is possible to apply the transformers defined on a model before modification in the database.

Passwords management
--------------------

The ``Password`` Transformer allows a field to be of the password type when displayed in an automatically generated CRUD form.

.. code-block:: php

   #[Transformer(name: "password")]
   private $password;

After submission from a form, it is possible to encrypt a password from the URequest class:

.. code-block:: php

   $encryptedPassword=URequest::password_hash('password');
   $user->setPassword($encryptedPassword);
   DAO::save($user);

The algorithm used in this case is defined by the php ``PASSWORD_DEFAULT``.

It is also possible to check a password entered by a user in the same way, to compare it to a hash:

.. code-block:: php

   if(URequest::password_verify('password', $existingPasswordHash)){
      //password is ok
   }


.. important:: Set up Https to avoid sending passwords in clear text.

Security module/ ACL management
===============================
In addition to these few rules, you can install if necessary:
   * :ref:`Ubiquity-acl<aclModule>`
   * :ref:`Ubiquity-security<securityModule>`