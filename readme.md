# Laravel Site

## Pre-requisites

> Make sure you are familiar with the [Contribution
> Guide][contribution-guide] and the main [development
> readme][development-readme].

* Make sure the [database and SMTP server][running-aux-services] are running
* Copy the `.env.example` file to `.env` and fill in the missing values

  > You may need to ask in Slack for some of the sensitive values

* Run the Nginx server using docker

  > Use [the scripts](#helpful-scripts) included in the to simplify this
  > process.

## How the nginx server works

This repository uses a Docker container that includes both nginx and
PHP-FPM to prevent everyone needing to install PHP and nginx and
configure their nginx server to correctly forward requests to PHP-FPM.

This Docker container is created using the [included
Dockerfile][local-dockerfile], which creates a volume mount between the
`/var/www` directory in the container (which is what nginx serves) and
`laravel-site`. This means that any changes you make locally are also
changed in the docker container, without the need to re-build and
restart the container.

> A caveat to this is that the file watcher between your computer and
> Docker can make your computer slow and can exceed the operating system
> limit. This can sometimes be resolved by stopping and then starting
> the container again.

## Helpful scripts

The [development repo][development-repo] includes a few helpful scripts
to simplify the process of running and developing with the site:

* `scripts/build-assets.sh`: Re-builds the JS/CSS files.
* `scripts/install-dependencies.sh`: Installs both the PHP and JS
  dependencies.
* `scrips/rebuild.sh`: Rebuilds the site's nginx server
* `scripts/start.sh`: Starts the site's nginx server, as well as the
  auxiliary services.
* `scripts/stop.sh`: Stops the site's nginx server, as well as the
  auxiliary services.
* `scripts/update.sh`: Pulls the latest changes and rebuilds the site
  (including updating the dependencies and re-building the assets).
* `scripts/watch-assets.sh`: Watches the assets so that changes are
  automatically re-built.

## License

This website uses code from Laravel and various packages, which retain
their original licenses (see each package for more details). The code
developed for this website is covered by the GNU General Public License
v2 (see the included LICENCE file).

[contribution-guide]: https://github.com/backstage-technical-services/hub/blob/master/Contributing.md
[development-readme]: https://github.com/backstage-technical-services/website-development/blob/v4.x/readme.md
[running-aux-services]: https://github.com/backstage-technical-services/website-development/blob/v4.x/readme.md#running-the-auxiliary-services
[development-repo]: https://github.com/backstage-technical-services/website-development/tree/v4.x
[local-dockerfile]: .docker/local.Dockerfile
