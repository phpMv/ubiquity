micro-framework installation
============================

Install Composer
----------------
**micro** utilizes Composer to manage its dependencies. So, before using, you will need to make sure you have `Composer <http://getcomposer.org/>`_ installed on your machine.

Install micro-devtools
----------------------
Download the Micro-devtools installer using Composer. ::
    composer global require "phpmv/micro-devtools=dev-master"

Make sure to place the `~/.composer/vendor/bin` directory in your PATH so the **Micro** executable can be located by your system.


Once installed, the simple micro new command will create a fresh micro installation in the directory you specify.
For instance, Micro new blog would create a directory named **blog** containing a fresh Micro installation with all dependencies installed. This method of installation is much faster than installing via Composer: ::
    Micro new blog

