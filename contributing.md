# Contributing

## Who can contribute?
Any Backstage member is encouraged to help with the maintenance of the site and development of new features, big or small. To get started simply fork the 
`master` branch.

Once you're done simply submit a Pull Request for the changes to be merged back into the `master` branch.

## Why do we have to submit Pull Requests?
Pull Requests are used as a quality check (to ensure that the new code is consistent with the existing code to avoid headaches in the 
future) and to ensure that what you've submitted doesn't conflict with anything else under development.

Anyone in the `Dev Leads` team can approve Pull Requests.

Anyone in the `Website Devs` team can push directly to a branch that isn't `master`.

## What if I can't programme?
If you can't programme, or can't spare the time to implement a feature yourself, then don't worry! All you need to do is [submit an issue](http://github.com/backstagetechnicalservices/website/issues) and someone on the development team will look into it.

## What do I need to be able to contribute?
It is assumed that those intending to develop this website have a sufficient knowledge of and experience in PHP, MySQL, Laravel and git. It is also recommended that you familiarise yourself with the best practices of each and the programming style already in use.

In order to run the website locally you need to have:

* **A web server**: As PHP 5.4 introduced a built-in server this is not required but for those intending to develop in the long term it is highly recommended
 you install Apache or nginx. You should be familiar with installing, configuring and operating your chosen web server. It is also strongly recommended you set up the website as a virtualhost.
* **PHP:** The upgrade to Laravel 5.6 means that you need to use at least PHP 7.1, although it is always recommended that you use the latest stable version.
* **Composer:** Composer is used by this website, Laravel and any included components to manage dependencies.
* **Database:** While any database storage solution supported by Laravel 5 can be used it is recommended you use MySQL 5.6+ (or MariaDB 10.2) to ensure full 
compatibility with the production server.
* **Node.js:** This is used to process the public assets using Laravel Mix which uses Webpack. You do not need to know how to use Node.js but do make sure you know how to run Mix.

## How do I install the site locally?
Ensure your web and MySQL servers are functioning correctly and create the database and user before installing the repository.

1. Clone the repository

    ```sh
    $ git clone https://github.com/backstagetechnicalservices/website.git
    ```
2. Install the dependencies

    ```sh
    $ composer install
    ```
3. Install Laravel Mix

    ```sh
    $ npm install
    ```
4. Create the environment file

    ```sh
    $ php -r "copy('.env.example', '.env');"
    ```
5. Populate the environment file

    ```sh
    $ php artisan key:generate
    ```
    > You will need to manually provide the other values; speak to one of the development team

6. Set up the database

    ```sh
    $ php artisan migrate
    ```
7. Populate the database

    > Speak to one of the development team to get a copy of the most recent database backup.

## Questions or need help?
If you have questions or need help at any stage, simply contact a member of the `Website Devs` team.