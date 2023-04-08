# Laravel Site

[![GitHub Actions](https://img.shields.io/github/actions/workflow/status/backstage-technical-services/laravel-site/deploy.yml?branch=main)](https://github.com/backstage-technical-services/laravel-site/actions)
[![chat](https://img.shields.io/badge/chat-on%20slack-brightgreen)](https://bts-website.slack.com)
[![license](https://img.shields.io/badge/license-Apache%20v2-blue)](./licence.md)

## Getting Started

### Pre-requisites

Make sure you are familiar with the [Contribution Guide][contribution-guide] and have
installed [the required prerequisites][prerequisites]. 

### Installing

Clone the repository:

```
git clone git@github.com:backstage-technical-services/laravel-site.git
```

This will be downloaded into a `laravel-site` folder, in your current directory.

### Configuring

- Copy the `.env.example` file to `.env`. You may need to [ask][slack] for some of the configuration values.
- Generate the site key using `scripts/site.sh artisan key:generate`.

## Usage

### Running the site

To avoid needing to install and configure Nginx, PHP-FPM, Composer, NPM and Yarn this site includes a Docker container
that includes all of this pre-build and pre-configured. This container creates a volume mount between `/var/www` (where
the site is configured to be served from) and the site directory. This means that any changes you make locally
are also changed in the docker container, without the need to re-build and restart the container.

> A caveat to this is that the file watcher between your computer and Docker can make your computer slow and can exceed
> the operating system limit. This can sometimes be resolved by stopping and then starting the container again.

As you will not have PHP etc installed on your computer, any commands you need to perform to manage the site will need
to be run on the Docker container. This repository also includes a [helpful script](scripts/site.sh) for managing the
site:

- `scripts/site.sh start` to start the site and auxiliary services.
- `scripts/site.sh stop` to stop the site and auxiliary services.
- `scripts/site.sh install` to install any dependencies.
- `scripts/site.sh watch-assets` to build JS/CSS assets and automatically re-build any changes.
- `scripts/site.sh artisan COMMAND` to run any artisan command.
- `scripts/site.sh test` to run any PHPUnit tests.
- `scripts/site.sh update` to update your copy of the site and install any new dependencies.
- `scripts/site.sh rebuild` to rebuild the container (eg, if the version of PHP changes).
- `scripts/site.sh help` to see more information.

### Auxiliary services

Included with this site are several auxiliary services to aid with local development:

- MariaDB database
- SMTP (mail) server

#### Connecting to the database

The database is exposed on port `6001` so you can connect to it using your database manager of choice. Just configure it
with the following:

- Host: `localhost`
- Port: `6001`
- Username: `developer`
- Password: `developer`

#### Initialising the data

The site does not seed any dummy data, but you can ask in [Slack][slack] for some initial data and then use your
database tool to import it.

#### Resetting the data

The database will keep any data by default, but if you want to reset your data you can either `TRUNCATE` the tables
using your database manager, or delete and recreate the container using `scripts/site.sh reset`.

#### Viewing emails

Included in the Docker set-up is a local SMTP server. This doesn't actually send any emails, so you're free to test with
any email address, and provides a user interface for viewing any emails that have been sent.

The site is automatically configured to connect to this over the internal port `25`, and you can view the web interface
at <http://localhost:6002>.

## License

This website uses code from Laravel and various packages, which retain their original licenses (see each package for
more details). The code developed for this website is covered by the GNU General Public License v2 (see the included
LICENCE file).

## CI/CD

This repository uses GitHub Actions for CI/CD.

Opening a PR will trigger the following checks:

- Tests, with SonarCloud

Merging to `main` will then deploy the site:

- Automatically deployed to <https://staging.bts-crew.com>
- Deploying to production can then be manually approved by an authorised user

[contribution-guide]: https://github.com/backstage-technical-services/hub/blob/main/Contributing.md
[prerequisites]: https://github.com/backstage-technical-services/hub/blob/master/docs/contributing/Developing.md#pre-requisites
[slack]: https://bts-website.slack.com
