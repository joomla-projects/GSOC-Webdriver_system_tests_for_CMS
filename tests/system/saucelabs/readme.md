# Security Restrictions when testing Pull Requests

The most important restriction for pull requests is about secure environment variables, or rather, any data that's encrypted with our encryption feature.

A pull request sent from a fork of the upstream repository could be manipulated to expose any environment variables. The upstream repository's maintainer would have no protection against this attack, as pull requests can be sent by anyone with a fork.

For the protection of secure data, Travis CI makes it available only on pull requests coming from the same repository. These are considered trustworthy, as only members with write access to the repository can send them.

More info at: http://docs.travis-ci.com/user/pull-requests/#Security-Restrictions-when-testing-Pull-Requests
