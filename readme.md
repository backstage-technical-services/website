# About
Backstage Technical Services is a student society at the University of Bath (UK) that specialises in providing technical support to a variety of shows and events held both on- and off-campus. This support can range from providing a simple PA to musical and theatrical shows to Freshers' Week and Summer Ball.

Backstage's unique position of supporting any and every event that requires technical support results in a busy schedule in need of careful administration to ensure the society can function as best as possible.

This began as a simple paper-based system for tracking the events Backstage had been booked for but was replaced by a website when the manual nature of the system became difficult to manage.

Over time this website has grown from an events tracker to a feature-rich system that allows Backstage to monitor both the events and its membership while also providing a centralised location for paperwork and some simple tools for socialising.

## Version History

### 2.x
Version 2 was built by Colin Paxton and later maintained by Lee Stone. It was written in PHP and used a MySQL database due to the limitations of hosting the site on the university's "personal home page" server. This site was well used by the membership and was continually improved by Lee and other interested members.

### 3.x
While Version 3 was never released it did exist in various stages of planning. This would use the same structure and hosting as Version 2 but would introduce several new features.

### 4.x
Due to its age Version 2 made use of numerous outdated or deprecated features and the process of continuous improvement had produced a file structure that was very difficult to manage and keep updated. The site also mixed HTML and PHP making the style difficult.

Version 4 does not introduce much additional functionality; instead it involved a re-write of Version 2 from the ground up, using a framework to promote modularity and aid with future development. This also enabled the creation of a responsive and "modern" design.

Version 4 also led the move of the server to a VPS, full use of the Backstage domain ([bts-crew.com](http://www.bts-crew.com)) and the use of git and GitHub to manage version control.

This development is led by Ben Jones ([bnjns](http://github.com/bnjns)) and is built on PHP 7, MySQL 5.6 and Laravel 5.4 and uses HTML5 and CSS3.

# Development
While the development is led by Ben Jones the use of git and GitHub allows any interested party to easily become involved in the development process.

Backstage members are welcome to fork the `master` branch and submit their changes using a Pull Request. Alternatively those who do not wish to or cannot programme can create an issue in [GitHub](http://github.com/backstagetechnicalservices/website/issues).

## Requirements
It is assumed that those intending to develop this website have a sufficient knowledge of and experience in PHP, MySQL, Laravel and git. It is also recommended that you familiarise yourself with the best practices of each and the programming style already in use.

In order to run the website locally you need to have:

**A web server**

As PHP 5.4 introduced a built-in server this is not required but for those intending to develop in the long term it is highly recommended you install Apache or Nginx. You should be familiar with installing, configuring and operating your chosen web server.

It is also strongly recommended you set up the website as a virtualhost.

**PHP**

As the website is currently built on PHP 5 the minimum PHP version is 5.4 however it is recommended you use PHP 7 to take advantage of the performance improvements and to avoid issues when the website moves to PHP 7 features.

**Composer**

Composer is used by this website, Laravel and any included components to manage dependencies.

**Database**

While any database storage solution supported by Laravel 5 can be used it is recommended you use MySQL 5.5+ (or MariaDB) to ensure full compatibility with the production server.

**Node.js**

This is used to process the public assets using Laravel Mix which uses Webpack. You do not need to know how to use Node.js but do make sure you know how to run Mix.

## Installation
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

    > You can no longer use `$ php artisan db:seed` to insert some default data; please speak to one of the development team to get a copy of the most recent database backup.
    
## Approval
Any approved member of the development team will be able to push changes to the "development" branch at any time - members of the development team are encouraged to keep their local copy updated.

Once a change has been tested and approved for release it can be added to the `master` branch using a Pull Request. This Pull Request should be tagged with the new version number.

Pull Requests can only be approved by the development lead. Once approved the production server will instantly synchronise and deploy the change.

# License
This website uses code from Laravel and various packages, which retain their original licenses (see each package for more details). The code developed for this website is covered by the GNU General Public License v2 (see the included LICENCE file).
