CORTEX
======

Description
-----------
COmputer-aided Registration Tool for EXperiments

Installation
------------
1. create a local clone of the whole repository using the following command (assumes you have git installed):
  * `git clone git@github.com:Niehztog/cortex.git`
2. in order to checkout the code of all external ressources aswell, invoke the following command:
  * `git submodule update --init --recursive`
3. The first thing to do after checkout is to edit file `include/config.php`
  * set all necessary configuration settings in that file
4. either [create](http://www.colostate.edu/~ric/htpass.html) a proper .htpasswd01 file (remember to reference it from within .htaccess) or remove .htaccess file _(not recommended)_
5. to initialize the database, call `create.php` in your webbrowser once
  * delete `create.php` afterwards for security reasons
  * __the system should be ready for use now__
6. _(optional)_ delete all obsolete files within the `externals` subfolder
  * _Hint:_ you will find a list of all mandatory files in the textfile `externals/.requiredFiles`
  
Troubleshooting
---------------
In case you see a "`Parse error`", make sure that in **php.ini** **short_open_tag** is enabled: `short_open_tag = On`