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
Due to its age Version 2 made use of numerous outdated or deprecated features and the process of continuous improvement had produced a file structure that was very difficult to manage and keep updated. The site also mixed HTML and PHP making updating the style difficult.

Version 4.0 did not introduce much additional functionality; instead it involved a re-write of Version 2 from the ground up, using a framework to promote modularity and aid with future development. This also enabled the creation of a responsive and "modern" design.

Version 4 also led the move of the server to a VPS, full use of the Backstage domain ([bts-crew.com](http://www.bts-crew.com)) and the use of git and GitLab to manage version control.

This development is led by [Ben Jones](http://github.com/bnjns) and is built on PHP 7, MySQL 5.6 and Laravel 6.0 and utilises Bootstrap 3.

# Contributing
See the [Contribution Guide](https://gitlab.com/backstage-technical-services/website/hub/-/blob/master/Contributing.md) for details on how to contribute;

TL;DR: Anyone can contribute; contact the secretary for more information.

# Bugs / Feature Suggestions
Report all bugs, feature suggestions and improvements through the [issue tracker](https://gitlab.com/backstage-technical-services/website/hub/-/issues/new). If you're unsure how to do this, please just ask a member of the [development team](https://gitlab.com/groups/backstage-technical-services/website/-/group_members) or the [secretary](mailto:sec@bts-crew.com).

# License
This website uses code from Laravel and various packages, which retain their original licenses (see each package for more details). The code developed for this website is covered by the GNU General Public License v2 (see the included LICENCE file).
