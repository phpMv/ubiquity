Ubiquity URLs
=================
like many other frameworks, if you are using router with it's default behavior, there is a one-to-one relationship between a URL string and its corresponding controller class/method.
The segments in a URI normally follow this pattern:

::
    example.com/controller/method/param
    example.com/controller/method/param1/param2...

Default method
--------------

When the URL is composed of a single part, corresponding to the name of a controller, the index method of the controller is automatically called :

**URL :**
::
    example.com/Products/1

**Controller :**
::
    class Products extends ControllerBase{
        public function index(){
            //Default action
        } 
    }


Required parameters
-------------------

If the requested method requires parameters, they must be passed in the URL :

**Controller :**
::
    class Products extends ControllerBase{
        public function display($id){} 
    }

**Valid Urls :**
::
    example.com/Products/display/1
    example.com/Products/display/10/
    example.com/Products/display/ECS

Optional parameters
-------------------

