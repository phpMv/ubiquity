.. _commands:
Ubiquity commands
=================
.. |br| raw:: html

   <br />

.. note:: This part is accessible from the **webtools**, so if you created your project with the **-a** option or with the **create-project** command..

Commands
--------

From the webtools, activate the **commands** part, 

.. image:: /_static/images/commands/commands-elm.png
   :class: bordered

or go directly to ``http://127.0.0.1:8090/Admin/commands``.

Commands list
~~~~~~~~~~~~~
Activate the **Commands** tab to get the list of existing devtools commands.

.. image:: /_static/images/commands/commands-list.png
   :class: bordered

Command info
~~~~~~~~~~~~
It is possible to get help on a command (which produces a result equivalent to ``Ubiquity help cmdName``).

.. image:: /_static/images/commands/command-help.png
   :class: bordered   

Command execution
~~~~~~~~~~~~~~~~~
Clicking on the run button of a command displays a form to enter the parameters (or executes it directly if it takes none).

.. image:: /_static/images/commands/command-run.png
   :class: bordered   
   
After entering the parameters, the execution produces a result.

.. image:: /_static/images/commands/command-exec.png
   :class: bordered   

Commands suite 
--------------
Return to **My commands tab**:
It is possible to save a sequence of commands (with stored parameters), and then execute the same sequence:

Suite creation
~~~~~~~~~~~~~~

Click on the **add command suite**

.. image:: /_static/images/commands/new-suite-btn.png
   :class: bordered

Add the desired commands and modify the parameters:

.. image:: /_static/images/commands/new-commands-suite.png
   :class: bordered

The validation generates the suite:


.. image:: /_static/images/commands/commands-suite-created.png
   :class: bordered

Commands suite execution
~~~~~~~~~~~~~~~~~~~~~~~~

Clicking on the run button of the suite executes the list of commands it contains:

.. image:: /_static/images/commands/commands-suite-exec.png
   :class: bordered

Custom command creation
-----------------------

Click on the **Create devtools command** button.

.. image:: /_static/images/commands/create-devtools-command-btn.png
   :class: bordered

Enter the characteristics of the new command:

  - The command name
  - The command value: name of the main argument
  - The command parameters: In case of multiple parameters, use comma as separator
  - The command description
  - The command aliases:  In case of multiple aliases, use comma as separator

.. image:: /_static/images/commands/create-command.png
   :class: bordered

.. note:: Custom commands are created in the **app/commands** folder of the project.


.. image:: /_static/images/commands/custom-command-exec.png
   :class: bordered

The generated class:

.. code-block:: php
   :linenos:
   :caption: app/commands/CreateArray.php
   
   namespace commands;
   
   use Ubiquity\devtools\cmd\commands\AbstractCustomCommand;
   use Ubiquity\devtools\cmd\ConsoleFormatter;
   use Ubiquity\devtools\cmd\Parameter;
   
   class CreateArray extends AbstractCustomCommand {
   
   	protected function getValue(): string {
   		return 'jsonValue';
   	}
   
   	protected function getAliases(): array {
   		return array("createarray","arrayFromJson");
   	}
   
   	protected function getName(): string {
   		return 'createArray';
   	}
   
   	protected function getParameters(): array {
   		return ['f' => Parameter::create('fLongName', 'The f description.', [])];
   	}
   
   	protected function getExamples(): array {
   		return ['Sample use of createArray'=>'Ubiquity createArray jsonValue'];
   	}
   
   	protected function getDescription(): string {
   		return 'Creates an array from JSON and save to file';
   	}
   
   	public function run($config, $options, $what, ...$otherArgs) {
   		//TODO implement command behavior
   		echo ConsoleFormatter::showInfo('Run createArray command');
   	}
   }
  
The **CreateArray** command implemented:

.. code-block:: php
   :linenos:
   :caption: app/commands/CreateArray.php
     
   namespace commands;
   
   use Ubiquity\devtools\cmd\commands\AbstractCustomCommand;
   use Ubiquity\devtools\cmd\ConsoleFormatter;
   use Ubiquity\devtools\cmd\Parameter;
   use Ubiquity\utils\base\UFileSystem;
   
   class CreateArray extends AbstractCustomCommand {
   
   	protected function getValue(): string {
   		return 'jsonValue';
   	}
   
   	protected function getAliases(): array {
   		return array(
   			"createarray",
   			"arrayFromJson"
   		);
   	}
   
   	protected function getName(): string {
   		return 'createArray';
   	}
   
   	protected function getParameters(): array {
   		return [
   			'f' => Parameter::create('filename', 'The filename to create.', [])
   		];
   	}
   
   	protected function getExamples(): array {
   		return [
   			'Save an array in test.php' => "Ubiquity createArray \"{\\\"created\\\":true}\" -f=test.php"
   		];
   	}
   
   	protected function getDescription(): string {
   		return 'Creates an array from JSON and save to file';
   	}
   
   	public function run($config, $options, $what, ...$otherArgs) {
   		echo ConsoleFormatter::showInfo('Run createArray command');
   		$array = \json_decode($what, true);
   		$error = \json_last_error();
   		if ($error != 0) {
   			echo ConsoleFormatter::showMessage(\json_last_error_msg(), 'error');
   		} else {
   			$filename = self::getOption($options, 'f', 'filename');
   			if ($filename != null) {
   				UFileSystem::save($filename, "<?php\nreturn " . var_export($array, true) . ";\n");
   				echo ConsoleFormatter::showMessage("$filename succefully created!", 'success', 'CreateArray');
   			} else {
   				echo ConsoleFormatter::showMessage("Filename must have a value!", 'error');
   			}
   		}
   	}
   } 
   
Custom command execution
~~~~~~~~~~~~~~~~~~~~~~~~

The new command is accessible from the devtools, as long as it is in the project:

.. code-block:: bash
   
   Ubiquity help createArray

.. image:: /_static/images/commands/custom-command-help.png
   :class: console
   
.. code-block:: bash
   
   Ubiquity createArray "{\"b\":true,\"i\":5,\"s\":\"string\"}" -f=test.php

.. image:: /_static/images/commands/custom-command-devtools.png
   :class: console
   