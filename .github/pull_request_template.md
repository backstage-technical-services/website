### Issue summary

Briefly describe the issue that this pull request resolves. Don't simply
copy over the text from the ticket - use this section to summarise it in
your own words. This gives context to the code review.

Ticket: https://github.com/backstage-technical-services/hub/issues/XXX

### Work included

Describe what work this pull request includes. There's no need to list
line-by-line (the change list will do that); instead just summarise each
bit of functionality that's been added, edited or removed. Link these
back to the issue summary if you can.

The "why" is just as (if not more) important than the "what" - so please
include any reasoning behind any decisions you made to help the reviewer
understand why you've done it that way.

Don't be afraid to use bullet points here!

### Testing

Outline how you should test this piece of work, including any
pre-requisites (eg, any new environment variables? do you need any API
keys or new accounts? do you need any resources?).

Describe how to test both happy and unhappy paths. What are the expected
outcomes? Are there any possible quirks you discovered during
development?

### Acceptance criteria

List all the criteria that need to be met for this work to pass QA.

### Links

Provide any links to any issues or other pull requests related to this
work.

### Checklist

> Make sure you follow these wherever possible; if you have then check
> it off, and if not then use a strikethrough (\~) to cross it off.

**General**

* [ ] Readme updated (including additional environment variables)
* [ ] Additional documentation written (if applicable)
* [ ] Good coverage of tests
* [ ] Updated docker config
* [ ] CI/CD config updated
* [ ] Docker image builds and boots

**Principles**

* [ ] DRY, SOLID and Clean
* [ ] Follows language code style
* [ ] Use consistent vocabulary
* [ ] Any tech debt justified and ticketed where appropriate
* [ ] All data access audited
* [ ] Appropriate level of logging
