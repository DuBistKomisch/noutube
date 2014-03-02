NoUTube
=======

YouTube subscription aggregator and organiser.

Written using PHP with the CodeIgniter framework.

Uses the Zend GData and PHPass libraries.

Requirements
============

 - PHP 5.2.x or higher
 - A web server, ideally with rewrite functionality (apache, nginx, etc.)
 - A database server (mysql, etc.)

Installation
============

Config
------

You'll need to modify these variables in application/config/config.php to get started:

 - `$config['base_url']`
   - your domain and subdirectory with trailing slash
 - `$config['encryption_key']` and `$config['sess_secure']`
   - (optional) add a key if you want to secure your sessions
 - `$config['sess_use_database']` and other session-related config variables
   - (optional) enable if you want to store sessions in your database (see below)
 - `$config['cookie_domain']` and `$['cookie_path']`
   - set to match the respective parts of your `$config['base_url']`

You'll also need to add a few of your own to the end of the file:

 - `$config['applicationID']`
   - I think this is actually ignored by the API now, meant to be like a User-Agent string
 - `$config['developerKey']`
   - obtained by going to the [Google API Console](http://code.google.com/apis/console) and creating a new project, then creating a new "server" "simple API access" "API key" (this is for the "old" console, good luck using the new one)
 - `$config['applicationName']`
   - the name to use to brand the site

Database
--------

Only MySQL is tested and probably works, since I used a few hacky queries.

Create a database and import the structure from the file `db.sql`. You should end up with 5 tables.

Create a user for the database and give them permissions needed to read and write rows.

Add the connection details to `application/config/database.php`. Be sure to set database debugging to `FALSE`.

If you want to store session data in a database too, refer to the [CodeIgniter Sessions documentation](http://ellislab.com/codeigniter/user-guide/libraries/sessions.html).

Cron
----

NoUTube requires a background service to regularly poll for new videos.

Simply run `crontab -e` and add the line:

`0 * * * * php /path/to/index.php videos poll`

There's plenty of documentation on how cron works, so I won't explain it here. Just suffice to say that this will run the service once an hour on the hour.

Rewrite
-------

This application requires the web server to internally rewrite the URL.

If you're using Apache, the included `.htaccess` will take care of it for you once you enable `mod_rewrite`.

If you're using nginx, add a location block like this to your site config after the php5-fpm block:

    location ~ /noutube/(.*)$ {
      try_files $uri /noutube/index.php?/$1;
    }

...where '/noutube' is the subdirectory in your `$config['base_url']`, change or remove as applicable.

Notes
-----

If you're going to be pushing commits back to github, you don't want your personalised config files to be included. While you can simply not add them to the commit, I suggest running the following commands to make git ignore changes made:

 `git update-index --assume-unchanged application/config/config.php`

 `git update-index --assume-unchanged application/config/database.php`
