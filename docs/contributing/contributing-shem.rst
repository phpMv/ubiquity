.. _contributing:
Contributing
============

System requirements
-------------------
Before working on Ubiquity, setup your environment with the following software:

- Git
- PHP version 7.1 or above.

Get Ubiquity source code
------------------------

On `Ubiquity github repository <https://github.com/phpMv/ubiquity>`_ :

- Click `Fork` Ubiquity project

- Clone your fork locally:

.. code-block:: bash
   
   git clone git@github.com:USERNAME/ubiquity.git


Work on your Patch
------------------

.. note::
   
   Before you start, you must know that all the patches you are going to submit must be released under the Apache 2.0 license, unless explicitly specified in your commits.


Create a Topic Branch
^^^^^^^^^^^^^^^^^^^^^

.. note::
   
   Use a descriptive name for your branch:

      - issue_xxx where xxx is the issue number is a good convention for bug fixes
      - feature_name is a good convention for new features

.. code-block:: bash
   
   git checkout -b NEW_BRANCH_NAME master

Work on your Patch
^^^^^^^^^^^^^^^^^^
Work on your code and commit as much as you want, and keep in mind the following:

- Read about the :ref:`Ubiquity coding standards<coding>`;
- Add unit, fonctional or acceptance tests to prove that the bug is fixed or that the new feature actually works;
- Do atomic and logically separate commits (use `git rebase` to have a clean and logical history);
- Write good commit messages (see the tip below).
- Increase the version numbers in any modified files, respecting `semver <https://semver.org>`_ rules:

   Given a version number ``MAJOR.MINOR.PATCH``, increment the:
   
   - ``MAJOR`` version when you make incompatible API changes,
   - ``MINOR`` version when you add functionality in a backwards-compatible manner, and
   - ``PATCH`` version when you make backwards-compatible bug fixes.

Submit your Patch
-----------------

Update the [Unrelease] part of the `CHANGELOG.md <https://github.com/phpMv/ubiquity/blob/master/CHANGELOG.md#changelog>`_ file by integrating your changes into the appropriate parts:

- Added
- Changed
- Removed
- Fixed

Eventualy rebase your Patch |br|
Before submitting, update your branch (needed if it takes you a while to finish your changes):

.. code-block:: bash

   git checkout master
   git fetch upstream
   git merge upstream/master
   git checkout NEW_BRANCH_NAME
   git rebase master

Make a Pull Request
-------------------

You can now make a pull request on `Ubiquity github repository <https://github.com/phpMv/ubiquity>`_ .

.. |br| raw:: html

   <br />  