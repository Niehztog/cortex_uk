CORTEX
======

Description
-----------
COmputer-aided Registration Tool for EXperiments
This is a webapplication written in PHP for organizing scientific experiments which require human participants. Cortex's main purpose is to offer an overview of running experiments, open timeslots for participating and to provide a signup mechanism for participants.

Prerequisites
-------------
You'll need...
*  [git](https://git-scm.com/) for checking out the submodules (like jquery) in their proper version
* a webserver running either Apache2 or Nginx, PHP >= 5.6, MySQL or MariaDB
* _optional:_ [docker](https://www.docker.com/) for setting up a development environment using docker

Installation
------------
1. create a local clone of the whole repository using the following command (assumes you have git installed):
  * `git clone git@github.com:Niehztog/cortex.git`
2. in order to checkout the code of all external ressources aswell, invoke the following command:
  * `git submodule update --init --recursive`
3. copy the files in the subfolder `src/public` to your webserver
4. edit the file `src/public/include/config.php`
  * set all necessary configuration settings in that file
  * most important settings are database host, username and password
5. either [create](http://www.colostate.edu/~ric/htpass.html) a proper .htpasswd01 file (remember to reference it from within .htaccess) or remove .htaccess file _(not recommended)_
6. to initialize the database, call `create.php` in your webbrowser once
  * delete `create.php` afterwards for security reasons
  * __the system should be ready for use now__
7. _(optional)_ delete all obsolete files within the `externals` subfolder
  * _Hint:_ you will find a list of all mandatory files in the textfile `externals/.requiredFiles`
