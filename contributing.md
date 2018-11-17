# Contributing

## Who can contribute?
Any Backstage member is encouraged to help with the maintenance of the site and development of new features, big or small. No matter what level of experience
 you have in website design or development - if you're interested in helping out then we would love your input.

## Can I contribute if I can't programme?
Yes.

There are many ways you can contribute, even if you don't have much programming experience. The simplest is simply
[submitting an issue](https://gitlab.com/backstage-technical-services/laravel-site/issues) for a bug, feature or improvement; this will let a member of the
development team look into it on your behalf. You can also make small changes or improvements to things like the layout or the wording, without any need to
programme.

If you're comfortable with programming, or want to get involved with the bigger stuff, then do join the development team to get stuck in.

## How does contributing work?
The website runs from the code on the `master` branch. No development is performed on this branch to ensure that the website remains fully functional. The
development branch (`develop`) is the primary branch used for development - there are a few ways that you can work from this branch:

* Those with write access to this branch can push their changes directly here, no matter how complete they are. However, this runs the risk of having a very
dirty commit history, and requires all developers keep their local copies of `develop` up-to-date, otherwise they will have a heck of a headache resolving
merge conflicts.
* If you want to work on something small or quick you can work on a fork of the repository. Once you've done, you can merge your changes back into the dev
branch using a [Merge Request](https://gitlab.com/backstage-technical-services/laravel-site/merge_requests/new).
* If you're working on something big, something that would benefit from multiple commits, or something that would benefit from other developers testing or
being involved with, it's recommended you create your own branch on the repository. This will allow you to work at your own pace, and let other developers
check your work as you go. Once complete, the changes can be merged back into the dev branch directly or with a Pull Request.

Once any changes have been confirmed as working with nothing that breaks functionality, they can be merged into the `master` branch.

## Why should we submit Merge Requests (MR)?
Merge Requests are used as a quality check (to ensure that the new code is consistent with the existing code to avoid headaches in the future) and to ensure
that what you've submitted doesn't conflict with anything else under development. Any group administrator or project maintainer can approve Merge Requests.

## Keeping the team informed
Any member of the development team **must** be a member of the GitLab team and the Slack channel; this is so that you have the necessary permissions to work
on the repository, but also so everyone can be kept up-to-date with what everyone is working on. It is vital that if you intend to work on something you let the rest of the team know - this is so that there won't be any issues with multiple members working on the same thing, and to ensure that what you're about to do is inline with the rest of the development.

Everyone in the development team is equal, but administrators do have the ability to refuse a MR or merge if it doesn't follow the planned direction of the website. With good communication, however, this won't ever be an issue!

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
    $ git clone git@gitlab.com:backstage-technical-services/laravel-site.git
    ```

2. Switch to the development branch

    ```sh
    $ git checkout develop
    ```

3. Install the dependencies

    ```sh
    $ composer install
    ```
4. Install any node modules

    ```sh
    $ npm install
    ```
5. Create the environment file

    ```sh
    $ php -r "copy('.env.example', '.env');"
    ```
6. Populate the environment file

    ```sh
    $ php artisan key:generate
    ```
    > You will need to manually provide the other values; speak to one of the development team

7. Set up the database

    ```sh
    $ php artisan migrate
    ```
8. Populate the database

    > Speak to one of the development team to get a copy of the most recent database backup.

## GitLab and Jira
GitLab is used as a way for other people to create issues, in the form of bug reports and improvement/feature requests. Internally, the team uses [Jira](https://jira.bts-crew.com) to manage the work needed to resolve an issue, or any other bug, feature or improvement that may be on the horizon. This separation allows us to break down our work into SMART tasks without cluttering the GitLab board and bothering the person who submitted it. Those not intending on working directly on the site do not need to sign up to Jira.

## Questions or need help?
If you have questions or need help at any stage, simply contact a member of the development team.
