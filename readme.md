# Laravel Site

[![GitHub Actions](https://img.shields.io/github/actions/workflow/status/backstage-technical-services/laravel-site/deploy.yml?branch=main)](https://github.com/backstage-technical-services/laravel-site/actions)
[![chat](https://img.shields.io/badge/chat-on%20slack-brightgreen)](https://bts-website.slack.com)
[![license](https://img.shields.io/badge/license-Apache%20v2-blue)](./licence.md)

## Pre-requisites

> Make sure you are familiar with the [Contribution
> Guide][contribution-guide] and the main [development
> readme][development-readme].

* Make sure the [database and SMTP server][running-aux-services] are
  running
* Copy the `.env.example` file to `.env` and fill in the missing values

  > You may need to ask in Slack for some of the sensitive values

* Run the Nginx server using docker

  > See the [development readme][development-readme]

## License

This website uses code from Laravel and various packages, which retain
their original licenses (see each package for more details). The code
developed for this website is covered by the GNU General Public License
v2 (see the included LICENCE file).

[contribution-guide]: https://github.com/backstage-technical-services/hub/blob/main/Contributing.md
[development-readme]: https://github.com/backstage-technical-services/website-development/blob/v4.x/readme.md
[running-aux-services]: https://github.com/backstage-technical-services/website-development/blob/v4.x/readme.md#running-the-auxiliary-services

