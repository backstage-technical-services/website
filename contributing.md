# Contributing
This document outlines the guidelines, rules, limitations and any other information needed in order to get involved with the development of the website. This document is very long as it intends to provide all the information necessary on the entire workflow, but please don't be daunted - there's no need to know everything from the off and any existing member of the team will be happy to help!

By contributing to this project in any way (from reporting issues to actual development) you are agreeing to abide by the guidelines, rules and limitations outlined in this document. However, you only need to agree to the parts which are relevant for your level of involvement (eg, you don't need to agree to the rules around how to develop if you only intend to report issues).

If you have any questions, or want to clarify anything, please just get in contact with any [existing team member][link-team-members].

## The Basics
### Who can contribute?
Any interested Backstage member or associate is encouraged to help with the maintenance of the site, and development of new features. Absolutely no experience in design or programming is necessary as we all love showing off by teaching people new skills. Of course, we also welcome anyone with lots of experience!

There's no required level of involvement - you can be involved as little or as much as you want.

### I can't programme - can I still contribute?
Yes, you most definitely can!

There are many ways in which you can contribute without programming, including (but not limited to):
* Reporting bugs
* Making minor fixes to the wording/layout (see [Contributing without access](#contributing-without-access))
* Defining the requirements or scope for a feature or improvement
* Testing changes pushed to the [development server][link-bts-dev]
* Assisting with the administration of the various development tools
* Helping keep the team sane

These may not sound as sexy as coding a completely new area of the website, but they are actually just as important!

* If you just want to [report issues or request new features][link-report-issue], you do not need to read this document anymore (however, we recommend you read the section on [GitLab](#gitlab) so you know the basics of how it works).
* If you want to get more involved with the development process please continue reading.

## Development tools
We use a variety of different tools to assist with the development process; it's recommended you glance over this section so you understand what each does, and which you need to use.

### GitLab
[GitLab][link-repository] houses the source code, and is responsible for source control and version management. GitLab also houses our [issue tracker][link-report-issue]; the public and society members can use this to report bugs or request features/improvements, and the team uses this for task management. To ensure traceability and enable us to gather more information from issue reporters, you will need to [create a GitLab account][gitlab-register].

To work on the repository you'll also need to be added to the [GitLab group][link-team-members]; either request access through GitLab or in [Slack](#slack).

It is possible to make very small contributions without being a member of either the GitLab group or Slack channel - fork the repository, make the change and submit a Merge Request. We strongly recommend against this route as we'd prefer to build an informed team, rather than use a disorganised "hit and run" approach. These types of Merge Requests will be assessed on a case-by-case basis.

An introduction to git (which GitLab is a web interface for) can be found [here][link-git-help].

#### Tickets vs issues
Within the team, we refer to issues in the issue tracker as "tickets"; we feel this better explains what the issues represent (after all, not all tickets are actually issues - they may be requests or even questions).

#### Use of labels
We use labels to categorise tickets:

* Labels beginning with `status: ` are used to indicate the stage the ticket is in (see [Development Workflow](#development-workflow)) and is used to organise the Kanban board. A ticket can only have 1 of these labels, or none if it's in the backlog or completed.
* Labels beginning with `module: ` are used to group tickets that are related to a particular functionality within the site. Any number of these labels can be applied to a ticket.
* Labels beginning with `type: ` are used to indicate the type of ticket (eg, `type: bug` for bugs and `type: feature` for feature requests). A ticket can only have 1 of these labels.
* `Blocked` is used to indicate that the ticket can't be worked on as it depends on another ticket to be completed first. The reason for the block will usually be described as a comment.
* `Waiting on Feedback` is used to indicate that more information is needed from the reporter before work can continue. Once work is resumed this label should be removed.
* `Needs Spec/Scope` is used for tickets in the backlog that need more information before they can be worked on.

> **Note:** Labels should only be applied to tickets, and not merge requests.

#### Milestones
Using milestones is optional, but they can be a useful way of tracking upcoming releases and organising tickets into a specific release. Only tickets should be assigned to a milestone.

If milestones are being used, it's important that you don't create a merge request to release a ticket that's not assigned to that release - doing so will result in the merge request being rejected.

### Slack
Good communication is vital, and ours happens on [Slack][link-slack]. We don't want anyone to be left out, so all discussions must happen here, no matter how big or small - please don't use external social media apps like Facebook, or even emails.

Slack is also the place where you can request access to the other tools we use.

To request an invite to our Slack workspace simply drop a quick message to [Ben](mailto:ben@bnjns.uk).

#### Channels
Slack makes use of channels to group conversations with a similar subject. By default, new members are only added to the `#general` channel, but you can add yourself to a channel by clicking on `Channels` in the left pane to see the full list. Alternatively, you can ask a Workspace Admin to add to you it. No channels are off limits to anyone (although some are there for Apps to post to so it doesn't make much sense to post there yourself).

Please keep channels on-topic, and refrain from discussing development with members individually - this is so that everyone is kept up-to-date and has an equal say in everything.

#### Notifications
Slack is very powerful when it comes to notifications - it allows you to customise notifications for each channel, with separate rules for both desktop and mobile.

The most common reason you might not know what's going on is if you have notifications off, or set to `Just mentions`.

#### Using mentions
You can mention people in a Slack message simply by including their username after an `@` symbol (if you type `@` Slack will give you some hints). Those with the correct permissions can also use `@here`, `@channel` and `@everyone` if the post is really important so everyone gets a notification.

#### Apps
Several Slack apps are installed, collecting notifications from our tools in 1 place to save us having to hop around the internet and check each tool individually.

Apps we currently have installed:
* Notifications of new issues in the `#gitlab` channel
* Notifications of pipelines in the `#gitlab` channel
* Notifications of production errors in the `#bugsnag` channel
* Notifications of full site backups in the `#backups` channel

If you want another integration installed, just ask a Workspace Admin.

### Bugsnag
[Bugsnag][link-bugsnag] is used to log errors and provide the team with all the information needed to resolve the error. We're currently on a free plan, so sharing isn't possible - but if you're working on an issue [Ben][link-ben] can provide you with any relevant information.

### One time secret
[Onetimesecret][link-onetimesecret] is used to share sensitive information, such as values for the environment file. You do not need an account to use this service.

## Working on GitLab
It's possible to make small, simple changes without doing anything on your local machine. This is really only for small wording/spelling changes, and any requests to change logic with this method will be rejected as they won't have been tested.

1. **Create a fork of the repository:** This makes a copy of the repository that's attached to your profile on GitLab.
3. **Make the changes:** Use the online editor to make the necessary changes. Go to the file you want to edit and click the `Edit` button. GitLab will help you with the process of creating commits to save the changes.
4. **Create a merge request:** This notifies us of your changes, and allows us to combine them back into the main repository. Once this is done, you can safely delete your fork.

## Working locally
Working locally is more complicated, but allows you to work on the code and test it before pushing any changes to the repository.

### Docker
We use [Docker][link-docker] to configure and run the site locally; make sure you've installed [Docker][link-docker-install] and [Docker Compose][link-docker-install-compose] before continuing.

It's recommended you are familiar with the following:

* [Images and containers][link-docker-images-vs-containers]
* [Running containers][link-docker-container-run]
* [Executing commands in containers][link-docker-container-exec]
* [Docker compose][link-docker-compose]

If you want to learn more, Docker's documentation is fantastic:

* [Docker][link-docker-docs]
* [Docker Compose CLI][link-docker-docs-compose-cli]
* [Docker Compose Files][link-docker-docs-compose-file]

> **Windows and macOS users beware:**
> 
> Neither Windows 10 nor macOS offer true native containerisation support (and macOS offers none at all). 
> This means that in order to function, Docker for Windows and Docker for Mac need to run a Linux VM, which is what actually runs the containers. 
> This VM has an additional RAM footprint of about 4GB, so make sure you have plenty of RAM.
>
> Because of this VM, the use of volumes (which is how we enable "hot-reloading" during development), can result in very long load times, making the site practically impossible to use. 
> 
> If you experience this, I recommend you manually run a Linux VM for both docker, the project files and your IDE. 
> Windows 10 users can now run some aspects of Ubuntu natively (see [below](#ubuntu-support-windows-only)), but I have no experience with this so you're on your own.
>
> Alternatively, dual boot with Linux (Ubuntu is incredibly easy to use). You may well find you prefer it ... 

### Install an IDE
You are welcome to use whatever IDE you choose, but we recommend PhpStorm by Jetbrains - it's industry leading and free for students!

### Ubuntu support (Windows only)
If you're developing on Windows, you can get by with plain old Windows, but we recommend you add the "Windows Subsystem for Linux" - this allows you to run Ubuntu almost natively within Windows.

This will let you use the included scripts to help with development.

See the [docs from Microsoft][link-windows-linux-support] for more information.

### Installing the site
Once you have the above configured, you can install the site:
1. Clone the repository
    ```sh
    $ git clone git@gitlab.com:backstage-technical-services/laravel-site.git
    ```
2. Create the environment file from the example file
    ```sh
    $ cp .env.example .env
    ```
3. Populate the environment file

   > If you're using Docker, most of the environment variables are populated for you. All you need to do are generate the site key (see [below](#some-helpful-commands)) and grab the rest from a member of the team.  

### Running the site
The docker set-up includes the following services:

* Nginx server
* PHP-FPM to serve the website
* MariaDB database
* Mail server

To start everything up, all you need to do is run

```sh
$ .docker/local/bin/start.sh
```
> The first time you run this Docker will need to download the relevant image layers; this may take a while but this is a one-time thing (well, until the images are updated). 

> The first time you boot the database, it will need to initialise itself and will restart a couple of times before it's ready to be used.

To stop everything, it's

```sh
$ .docker/local/bin/stop.sh
```

### Some helpful commands
Here are some helpful commands for interacting with the PHP container:

* Generate an app key:

  ```sh
  $ docker exec bts_php sh -c "php artisan key:generate"
  ```

* Run any database migrations:

  ```sh
  $ docker exec bts_php sh -c "php artisan migrate"
  ```
  
* View the logs:
    * PHP:
      ```sh
      $ docker logs bts_php
      ```
    * Database:
      ```sh
      $ docker logs bts_mysql
      ```
    * Nginx:
      ```sh
      $ docker logs bts_nginx
      ```

* Pull any changes from the repository and update any assets

  ```sh
  $ .docker/local/bin/update.sh
  ```
  
* Continually re-build assets when they change

  ```sh
  $ .docker/local/bin/watch-assets.sh
  ```

You can enter the container with an interactive bash shell with

```sh
$ docker exec -ti bts_php bash
```

From here, you can run any command as if you were in a normal terminal session.

> Because the site uses a volume for the files, you can make changes either in your own operating system or in the container and they'll be reflected in the other. 
> The only thing you can't do is run docker commands.

### Viewing emails
Included in the Docker set-up is a local SMTP server. This doesn't actually send any emails, so you're free to test with any email address, and provides a user interface for viewing any emails that have been sent.

You can view this interface at [http://localhost:8001](http://localhost:8001) (unless you've changed the value of `PORT_MAIL` in your `.env` file).

### Connecting to the MySQL database
The MySQL database is exposed to your own computer so you can connect to it using the editor of your choice:

* Host: `localhost`
* Port: `6001` (unless you've changed the value of `PORT_MYSQL` in your `.env` file)
* Username: `developer` (unless you've changed the value of `DB_USERNAME` in your `.env` file)
* Password: `developer` (unless you've changed the value of `DB_PASSWORD` in your `.env` file)

### Understanding the Website

The website is built using PHP 7, with a framework called Laravel; their [documentation][link-laravel-docs] is the best place to start to familiarise yourself with the organisation of the website.

It's also recommended you familiarise yourself with [SASS][link-sass] as this is used to process the stylesheets.

## Development Workflow
See the [Development Workflow wiki][link-wiki-workflow].

## Questions or need help?
If you get stuck or need help, then just send a message on the Slack workspace.

[link-team-members]: https://gitlab.com/groups/backstage-technical-services/-/group_members
[link-repository]: https://gitlab.com/backstage-technical-services/laravel-site
[link-report-issue]: https://gitlab.com/backstage-technical-services/laravel-site/issues
[link-merge-request]: https://gitlab.com/backstage-technical-services/laravel-site/merge_requests/new
[link-slack]: https://bts-website.slack.com
[link-bugsnag]: https://app.bugsnag.com/backstage-technical-services
[link-onetimesecret]: https://onetimesecret.com
[link-phpstorm]: https://www.jetbrains.com/phpstorm
[gitlab-register]: https://gitlab.com/users/sign_in#register-pane
[link-ben]: https://www.gitlab.com/bnjns
[link-laravel-docs]: https://laravel.com/docs/5.6
[link-bts-dev]: https://dev.bts-crew.com/
[link-git-help]: https://guides.github.com/introduction/git-handbook/
[link-sass]: https://sass-lang.com/guide
[link-gitlab-board]: https://gitlab.com/backstage-technical-services/laravel-site/boards/855759
[link-wiki-workflow]: https://gitlab.com/backstage-technical-services/laravel-site/wikis/Development-Workflow
[link-docker]: https://www.docker.com/
[link-docker-install]: https://docs.docker.com/install/
[link-docker-install-compose]: https://docs.docker.com/compose/install/
[link-docker-images-vs-containers]: https://stackoverflow.com/a/23736802
[link-docker-container-run]: https://docs.docker.com/engine/reference/commandline/container_run/
[link-docker-container-exec]: https://docs.docker.com/engine/reference/commandline/container_exec/
[link-docker-compose]: https://docs.docker.com/compose/
[link-docker-docs]: https://docs.docker.com/engine/reference/commandline/cli/
[link-docker-docs-compose-cli]: https://docs.docker.com/compose/reference/
[link-docker-docs-compose-file]: https://docs.docker.com/compose/compose-file/
[link-windows-linux-support]: https://docs.microsoft.com/en-us/windows/wsl/install-win10