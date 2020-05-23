# Using the example project

The example folder contain a stock Laravel 7.x prooject with several presets defined, used by models, controllers, and Nova resources.

## Installation

The projet may use Laravel Nova, but it  is not open-source so it is not installed (or installable) by default.  

If you do not want to use Nova with the exemple projet, edit its composer.json file to remove the line where `laravel/nova` is required.

If you do want to use Nova, you have to install its source in the `nova` folder. Then after runngin composer install, run 

In all cases, run `composer install` to install dependencies.

If you want to use Nova you then have actually install assets run the `php artisan nova:install` command.

Then update if needed the database informations in the `.env` file.

Create the database, then run the php artisan migrate command, which migrate and seed the database.

Once done you should be able to launch the built-in webserver with `php artisan serve`.

