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

> **Note:** Labels should only be applied to labels, and not merge requests.

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

### Mailtrap
[Mailtrap][link-mailtrap] is a tools used for testing emails - it provides an inbox so that we can fully test the website but any test emails don't actually get sent. If you wish to use this to test emails you must create a free account and update the credentials in the .env file.

### One time secret
[Onetimesecret][link-onetimesecret] is used to share sensitive information, such as values for the environment file. You do not need an account to use this service.

## Working on GitLab
It's possible to make small, simple changes without doing anything on your local machine. This is really only for small wording/spelling changes, and any requests to change logic with this method will be rejected as they won't have been tested.

1. **Create a fork of the repository:** This makes a copy of the repository that's attached to your profile on GitLab.
3. **Make the changes:** Use the online editor to make the necessary changes. Go to the file you want to edit and click the `Edit` button. GitLab will help you with the process of creating commits to save the changes.
4. **Create a merge request:** This notifies us of your changes, and allows us to combine them back into the main repository. Once this is done, you can safely delete your fork.

## Working locally
Working locally is more complicated, but allows you to work on the code and test it before pushing any changes to the repository.

### Pre-requisites
In order to develop locally, you'll need to have the following installed:
* PHP 7.1+
* Nginx or Apache
   > Nginx and PHP-FPM is recommended as they're used on the production server
* MariaDB 10.2 or MySQL 5.5+
    > MariaDB 10.2 is recommended as it's used on the production server
* Composer
    > Composer is a package manager for PHP. You don't need to know how this works, just how to run the `install` command
* Node.js and yarn
    > Yarn is used to manage the asset dependencies and compile the assets. You don't need to know how this works, just how to run the `install` and `run` commands. We used to use npm, but yarn is much better.
* A PHP-focussed IDE
    > You can technically use Notepad, but it's recommended you use an IDE to make development easier. We recommend [PhpStorm by JetBrains][link-phpstorm] (students can get a free licence).

Adding instructions for how to set up and configure these for the various operating systems and distributions is way beyond the scope of this document. There are a lot of good tutorials out there, but one of the development team will be happy to help you get set up if you get stuck.

### Using virtual hosts
You can set up your web server to just run on `localhost`, but if you want to develop multiple sites it's recommended that you set up virtual hosts. A quick Google should help, but if not a member of the development team will definitely be able to.

### Installing the site
Once you have the above configured, you can install the site:
1. Clone the repository
    ```sh
    $ git clone git@gitlab.com:backstage-technical-services/laravel-site.git
    ```
2. Install any PHP dependencies
    ```sh
    $ composer install
    ```
3. Install any asset dependencies
    ```sh
    $ yarn install
    ```
4. Create the environment file from the example file
    ```sh
    $ php -r "copy('.env.example', '.env');"
    ```
5. Populate the environment file

    1. Set `APP_ENV` to 'local'
    2. Set `APP_DEBUG` to `true`
    3. Set `APP_URL` to the base url of the site
    4. Set the `DB_*` variables to connect to your MySQL server
    5. Run `$ php artisan key:generate` to create an encryption key
    6. Populate the rest of the details
        > Ask a maintainer who will be able to share them using onetimesecret

6. Set up the database structure
    ```sh
    $ php artisan migrate
    ```
    > This will only set up the table structure; you'll need to get the data from a Maintainer.

7. Compile the assets into plain stylesheets and javascript files
    ```sh
    $ yarn dev
    ```

### Understanding the Website

The website is built using PHP 7, with a framework called Laravel; their [documentation][link-laravel-docs] is the best place to start to familiarise yourself with the organisation of the website.

It's also recommended you familiarise yourself with [SASS][link-sass] as this is used to process the stylesheets.

### Keeping your local copy up-to-date
It's important to keep your local copy of the website up-to-date, as others work on bugs and features. To merge in the latest changes, simply run

```sh
$ git pull origin <branch_name>
```

You may then have to update the dependencies and database structure:
* Update the PHP dependencies
    ```sh
    $ composer install
    ```
* Update the asset dependencies and re-compile them
    ```sh
    $ yarn install
    $ yarn dev
    ```
* Update the database structure
    ```sh
    $ php artisan migrate
    ```
* Clear any cached views and configuration
    ```sh
    $ php artisan cache:clear
    ```

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
[link-mailtrap]: https://mailtrap.io
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