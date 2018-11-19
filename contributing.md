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
* Making minor fixes to the wording/layout (see [Contributing without access][contributing-without-access])
* Defining the requirements or scope for a feature or improvement
* Testing changes pushed to the [development server][link-bts-dev]
* Assisting with the administration of the various development tools
* Helping keep the team sane

These may not sound as sexy as coding a completely new area of the website, but they are actually just as important!

* If you just want to [report issues or request new features][link-report-issue], you do not need to read this document anymore (however, we recommend you read the section on [GitLab][gitlab] so you know the basics of how it works).
* If you want to get more involved with the development process please continue reading.

## Development tools
We use a variety of different tools to assist with the development process; it's recommended you glance over this section so you understand what each does, and which you need to use.

### GitLab
[GitLab][link-repository] houses the source code, and is responsible for source control and version management. GitLab also houses our [issue tracker][link-report-issue] where the public and members can report bugs or request features/improvements. To ensure traceability and enable us to gather more information from issue reporters, you will need to [create a GitLab account][gitlab-register].

To work on the repository you'll also need to be added to the [GitLab group][link-team-members]; either request access through GitLab or in [Slack][slack].

It is possible to make very small contributions without being a member of either the GitLab group or Slack channel - fork the repository, make the change and submit a Merge Request. We strongly recommend against this route as we'd prefer to build an informed team, rather than use a disorganised "hit and run" approach. These types of Merge Requests will be assessed on a case-by-case basis.

An introduction to git (which GitLab is built upon) can be found [here][link-git-help].

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

### Jira
[Jira][link-jira] is currently being trialled as an addition to GitLab. It is used internally by the team to discuss issues and break them down into more manageable tasks in a way that doesn't disturb the GitLab reporter. Jira also provides us with far more power over issues by providing multiple ticket types rather than the single available in GitLab. This allows us to tailor our [Development Workflow][development-workflow] to the ticket type.

To get an account, send a message in Slack and an administrator will send you an invitation. Jira can be a bit of a beast to learn, so don't hesitate to ask for help or tips!

### Bugsnag
[Bugsnag][link-bugsnag] is used to log errors and provide the team with all the information needed to resolve the error. We're currently on a free plan, so sharing isn't possible - but if you're working on an issue [Ben][link-ben] can provide you with any relevant information.

### Mailtrap
[Mailtrap][link-mailtrap] is a tools used for testing emails - it provides an inbox so that we can fully test the website but any test emails don't actually get sent.

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

### Understanding the Website

The Website is built using the Laveral framework, their [documentation][link-laveral-docs] are the best place to start looking to understand the website structure.


## Development Workflow
To ensure consistency throughout the team, and to minimise the risk of releasing broken code, this project uses a set of pre-defined workflows that all team members must use. The workflow that applies depends on whether you're working on a [standard ticket][standard-tickets] or a [hotfix][hotfixes].

This project uses the Kanban system; all tickets must go through the stages sequentially, and each stage has a set of criteria that the ticket must meet before it is allowed to enter that stage. No one should work on something that is not a ticket on Jira, no matter how small.

If you are intending on working on the site, please make sure you have read this section fully so you understand how the Kanban board is used, and the process works locally.

### Stages
* `Backlog`: This is where all new tickets arrive. These can either come from GitLab, or are manually created by a Maintainer. You must not work on a ticket in this area, as it may not be well-defined or ready to be worked upon.
     > Feel like a ticket should be moved to `Selected for Development`? Have a chat with a Maintainer.
* `Selected for Development`: This stage is for all tickets that are ready to be worked on. A Maintainer has deemed these tickets have a clear and measurable objective, and are suitable for working on. Once a ticket is moved to this stage anyone may assign that ticket to themselves, indicating that they intend to work on it in the near future.
    >  Whilst assigned to a ticket you are in charge of it - however you are more than welcome to enlist other team members to help!

    > Don't want to work on a ticket anymore? Simply unassign yourself allowing someone else can pick it up.
* `In Progress`: This stage is for tickets that are currently being worked on. Tickets sit here until all the work is complete and meets the objective.
* `QA`: This is the most important stage of the process. Once a ticket has been finished, it is moved to this stage so that a Maintainer can assess the code. This assessment process has 2 aims:
    1. Code review: The completed work should achieve the objective of the ticket, not introduce any unplanned conflicts, and maintain consistency with the code style.
    2. Verification: The completed work is tested on the development environment to ensure it does not introduce further issues across the entire site.

    It is very possible that a ticket will not pass this stage first time, so there may be some back-and-forth between the `In Progress` and `QA` stages. This is perfectly normal, and does not mean that the work has not been done well. This stage is here to protect the production server and ensure the site remains fully functional.
* `Ready to Merge`: Once a ticket is verified, it is ready to be deployed to the server. Tickets are moved here to indicate that they are waiting for their code to be merged into the `master` branch. Only the Maintainer performing the verification in `QA` can move the ticket to this stage.
* `Released (Done)`: Once a ticket has been merged into the `master` branch and deployed to the production server it is moved to this stage, where it drops off the Kanban board. This process should be performed by the Owner of the project, but can be performed by a Maintainer if necessary.

### Standard tickets
A `Standard ticket` applies to 99.9% of the work performed; unless the ticket meets the criteria for a hotfix (see [here][hotfixes]) it is classed as a standard ticket.

Standard tickets go through all of the development stages:
* Backlog
* Selected for Development
* In Progress
* QA
* Ready to Merge
* Released (Done)

All work is branched from and merged into `develop`.

### Hotfixes
A `hotfix` is a piece of work that is considered to be critical and must be fixed as soon as possible. As a result, it is subjected to a much shorter and less rigid development workflow.

The definition of a hotfix is somewhat arbitrary, but a couple of examples are:
* A bug that is currently making the production server unusable
* A change that is small enough that the development cycle for a standard ticket is overkill

Only a Maintainer is allowed to classify a ticket as a `Hotfix` due to the elevated risk of deploying broken code.

#### Development stages
The development stages for a hotfix are:
* Backlog
* In Progress
* QA
* Released (Done)

In this case, the `QA` stage is significantly shorter, only requiring that a Maintainer verifies the hotfix resolves the ticket.

### Process
Once a ticket is moved to `Selected for Development` and someone has been assigned to it, it can be worked on locally. Any work should be performed on its own branch, the name of which should include both the ticket number and a shortened ticket summary. Standard tickets should be branched from `develop`, while hotfixes are branched from `master`. Make sure you are working from the latest copy of this branch by first performing `$ git pull`.

> An example of a good branch name is: `bls-3--recurring-events`
>
> An example of a bad branch name is: `add-recurring-option`

When you start working on the branch, move the ticket from `Selected for Development` to `In Progress`. It's recommended that those working on the branch commit their work frequently to reduce the likelihood of merge conflicts.

Once the work is completed, the final commit should be pushed and then a [Merge Request][link-merge-request] created. The source branch should be set to the branch that's been worked on, and the target branch set to `develop`. Once the Merge Request is submitted, the ticket can be moved from `In Progress` to `QA`. The code then enters the first phase of QA: the code review.

In the code review, the Maintainer will ensure the ticket is resolved by the Merge Request and there are no unplanned conflicts with any other tickets or future work. If changes are needed, the Merge Request will be 'rejected' (although it can remain open as any further commits will be automatically added to the Merge Request), and the Maintainer will explain what needs to be done before it's submitted again. This effectively moves the ticket back to the `In Progress` stage (although it's not necessary to actually do this). This process repeats until the Merge Request is approved.

When the Merge Request is approved, the code is merged into the `develop` branch and it enters the 2nd phase of `QA`: verification. Extensive testing is performed on the [development server][link-bts-dev] to ensure that the new code performs as desired and doesn't introduce any further issues. How long this process lasts depends on how complex the change was, and the level of risk associated with it.

Should the change fail the verification phase and it can't be fixed with a hotfix on the `develop` branch, the Owner or a Maintainer will need to manually unpick the changes so the process can start again. If the appropriate amount of testing has been performed before the Merge Request was submitted, it is almost impossible that this will happen - if it does, though, there'll be plenty of support to rectify the problem.

Once the code passes the verification phase, a Maintainer can create a Merge Request to merge the changes from `develop` to `master`. Another quick review and verification will be performed in the Merge Request itself, before being approved. Once approved, the new code is then deployed to the production server and the ticket can be marked as `Released (Done)`.

#### Hotfixes
Hotfixes follow almost the same process as standard tickets, but with the following differences:
* Hotfix branches are created from `master`, rather than `develop`.
* When creating a Merge Request, set the target branch to `master`, rather than `develop`.
* Hotfixes only undergo a quick code review and verification within the Merge Request. If this isn't possible, the hotfix will need to be converted to a standard ticket.
* Hotfixes do not interact with the `develop` branch at all - it is therefore necessary for a Maintainer to merge the hotfix into `develop` from `master` manually. All developers will then need to update their local copies of `develop`.

## Questions or need help?
If you get stuck or need help, then just send a message on the Slack workspace.

[link-team-members]: https://gitlab.com/groups/backstage-technical-services/-/group_members
[link-repository]: https://gitlab.com/backstage-technical-services/laravel-site
[link-report-issue]: https://gitlab.com/backstage-technical-services/laravel-site/issues
[link-merge-request]: https://gitlab.com/backstage-technical-services/laravel-site/merge_requests/new
[link-slack]: https://bts-website.slack.com
[link-jira]: https://jira.bts-crew.com
[link-bugsnag]: https://app.bugsnag.com/backstage-technical-services
[link-mailtrap]: https://mailtrap.io
[link-onetimesecret]: https://onetimesecret.com
[link-phpstorm]: https://www.jetbrains.com/phpstorm
[gitlab-register]: https://gitlab.com/users/sign_in#register-pane
[link-ben]: https://www.gitlab.com/bnjns
[link-laveral-docs]: https://laravel.com/docs/5.7
[link-bts-dev]: https://dev.bts-crew.com/
[link-git-help]: https://guides.github.com/introduction/git-handbook/