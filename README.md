CORTEX
======

Description
-----------
COmputer-aided Registration Tool for EXperiments

This is a webapplication written in PHP for organizing scientific experiments which require human participants. Cortex's main purpose is to offer an overview of running experiments, open timeslots for participation and to provide a signup mechanism for participants.

Prerequisites
-------------
You'll need...
*  [Git](https://git-scm.com/) for checking out the submodules (like jquery) in their proper version
* A webserver running either Apache2 or Nginx, PHP >= 5.6, MySQL or MariaDB
* _Optional:_ [Docker](https://www.docker.com/) for setting up a development environment using docker

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

Additional information for usage with Docker
--------------------------------------------
There are configuration files for Docker included in this project. The Docker port consists of three images, one for Nginx, one for PHP FPM and one for MariaDB. The Docker setup is for testing and development purposes only as there are some settings and modules (like PHP Xdebug) included which would not suit a production environment. Of course it would be possible to make the images ready for a production environment by some small changes.
Steps for starting up Docker:
* edit the file `docker-compose.yml`, insert your local Docker network IP next to `XDEBUG_CONFIG` and configure MariaDB password and username.
* delete the directory `database\mariadb` (it contains an example database and will be recreated with the username and password you specified in the `docker-compose.yml` file earlier once you start Docker for the first time)
* type `docker-compose up` in the project's directory to start up Docker
* open `http://localhost/index.php` in your webbrowser
