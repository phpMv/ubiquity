.. _coding:
Coding guide
============

.. note::
   
   Although the framework is very recent, please note some early Ubiquity classes do not fully follow this guide and have not been modified for backward compatibility reasons. |br|
   However all new codes must follow this guide.

Design choices
--------------
Fetching and using Services
^^^^^^^^^^^^^^^^^^^^^^^^^^^
Dependency injections
*********************
Avoid using dependency injection for all parts of the framework, internally. |br|
Dependency injection is a resource-intensive mechanism:

- it needs to identify the element to instantiate ;
- then to proceed to its instantiation ;
- to finally assign it to a variable.

Getting services from a container
*********************************
Also avoid public access to services registered in a service container. |br|
This type of access involves manipulating objects whose return type is unknown, not easy to handle for the developer.

For example,
It's hard to manipulate the untyped return of ``$this->serviceContainer->get('translator')``, as some frameworks allow,
and know which methods to call on it.

When possible, and when it does not reduce flexibility too much, the use of static classes is suggested:

For a developer, the ``TranslatorManager`` class is accessible from an entire project without any object instantiation. |br|
It exposes its public interface and allows code completion:

- The translator does not need to be injected to be used;
- It does not need to be retrieved from a service container.

The use of static classes inevitably creates a strong dependency and affects flexibility. |br|
But to come back to the Translator example, there is no reason to change it if it is satisfying. |br|
It is not desirable to want to provide flexibility at all costs when it is not necessary, and then for the user to see that its application is a little slow.

Optimization
------------
Execution of each line of code can have significant performance implications. |br|
Compare and benchmark implementation solutions, especially if the code is repeatedly called:

- Identify these repetitive and expensive calls with php profiling tools (`Blackfire profiler <https://blackfire.io>`_ , `Tideways <https://tideways.com>`_ ...)
- Benchmark your different implementation solutions with `phpMyBenchmarks <https://phpMyBenchmarks.kobject.net>`_

Code quality
------------
Ubiquity use `Scrutinizer-CI <https://scrutinizer-ci.com/g/phpMv/ubiquity/>`_ for code quality.

- For classes and methods :

  - A or B evaluations are good
  - C is acceptable, but to avoid if possible
  - The lower notes are to be prohibited

Code complexity
^^^^^^^^^^^^^^^

- Complex methods must be split into several, to facilitate maintenance and allow reuse;
- For complex classes , do an extract-class or extract-subclass refactoring and split them using Traits;

Code duplications
^^^^^^^^^^^^^^^^^
Absolutely avoid duplication of code, except if duplication is minimal, and is justified by performance.

Bugs
^^^^
Try to solve all the bugs reported as you go, without letting them accumulate.

Tests
-----
Any bugfix that doesn’t include a test proving the existence of the bug being fixed, may be suspect. |br|
Ditto for new features that can’t prove they actually work.

It is also important to maintain an acceptable coverage, which may drop if a new feature is not tested.

Code Documentation
------------------
The current code is not yet fully documented, feel free to contribute in order to fill this gap.

Coding standards
----------------

Ubiquity coding standards are mainly based on the `PSR-1 <https://www.php-fig.org/psr/psr-1/>`_ , `PSR-2 <https://www.php-fig.org/psr/psr-2/>`_ and `PSR-4 <https://www.php-fig.org/psr/psr-4/>`_ standards, so you may already know most of them. |br|
The few intentional exceptions to the standards are normally reported in this guide.

Naming Conventions
^^^^^^^^^^^^^^^^^^

- Use camelCase for PHP variables, members, function and method names, arguments (e.g. $modelsCacheDirectory, isStarted());
- Use namespaces for all PHP classes and UpperCamelCase for their names (e.g. CacheManager);
- Prefix all abstract classes with Abstract except PHPUnit BaseTests;
- Suffix interfaces with ``Interface``;
- Suffix traits with ``Trait``;
- Suffix exceptions with ``Exception``;
- Suffix core classes manager with ``Manager`` (e.g. CacheManager, TranslatorManager);
- Prefix Utility classes with ``U`` (e.g. UString, URequest);
- Use UpperCamelCase for naming PHP files (e.g. CacheManager.php);
- Use uppercase for constants (e.g. const SESSION_NAME='Ubiquity').

Indentation, tabs, braces
^^^^^^^^^^^^^^^^^^^^^^^^^

- Use Tabs, not spaces; (!PSR-2)
- Use brace always on the same line; (!PSR-2)
- Use braces to indicate control structure body regardless of the number of statements it contains;

Classes
^^^^^^^

- Define one class per file;
- Declare the class inheritance and all the implemented interfaces on the same line as the class name;
- Declare class properties before methods;
- Declare private methods first, then protected ones and finally public ones;
- Declare all the arguments on the same line as the method/function name, no matter how many arguments there are;
- Use parentheses when instantiating classes regardless of the number of arguments the constructor has;
- Add a use statement for every class that is not part of the global namespace;

Operators
^^^^^^^^^

- Use identical comparison and equal when you need type juggling;

Example

.. code-block:: php
   
	<?php
	namespace Ubiquity\namespace;

	use Ubiquity\othernamespace\Foo;

	/**
	 * Class description.
	 * Ubiquity\namespace$Example
	 * This class is part of Ubiquity
	 *
	 * @author authorName <authorMail>
	 * @version 1.0.0
	 * @since Ubiquity x.x.x
	 */
	class Example {
		/**
		 * @var int
		 *
		 */
		private $theInt = 1;
	
		/**
		 * Does something from **a** and **b**
		 *
		 * @param int $a The a
		 * @param int $b The b
		 */
		function foo($a, $b) {
			switch ($a) {
				case 0 :
					$Other->doFoo ();
					break;
				default :
					$Other->doBaz ();
			}
		}
		
		/**
		 * Adds some values
		 *
		 * @param param V $v The v object
		 */
		function bar($v) {
			for($i = 0; $i < 10; $i ++) {
				$v->add ( $i );
			}
		}
	}


.. important::
   
   You can import this standardization files that integrates all these rules in your IDE:
     - :download:`Eclipse </contributing/phpMv-coding-standards.xml>`
     - :download:`PhpStorm </contributing/phpMv-coding-standards-storm.xml>`
    If your preferred IDE is not listed, you can submit the associated standardization file by creating a new PR.

.. |br| raw:: html

   <br />  