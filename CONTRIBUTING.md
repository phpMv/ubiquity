# Contributing to Ubiquity

First off, thank you to intend to contribute, and to take time to do. :+1:

The following is a set of guidelines for contributing to Ubiquity.

## How Can I Contribute?
### Reporting Bugs
- Make sure you’re on the latest version
- Ensure you're in the right project to report
- Ensure the bug was not already reported by searching on GitHub under [issues](https://github.com/phpMv/ubiquity/issues)
- If you're unable to find an open issue addressing the problem, open a [new one](https://github.com/phpMv/ubiquity/issues/new/choose). 
### Suggesting new features or enhancements
Enhancement suggestions are tracked as [GitHub issues](https://guides.github.com/features/issues/).
- Perform a [cursory search](https://github.com/search?q=is%3Aissue+user%3Aphpmv) to see if the enhancement has already been suggested. If it has, add a comment to the existing issue instead of opening a new one.
- Suggest your change by creating an [issue](https://github.com/phpMv/ubiquity/issues/new/choose) 
- Explain why this enhancement would be useful to most Ubiquity users
  - If your change is relative to the core of the framework (Http objects, router, controllers, models, orm, cache system, database, contents), add the mention `[RFC]` in his title
  - Wait for the tag `accepted` from the maintainers
- Write your code respecting [coding guide](https://micro-framework.readthedocs.io/en/latest/contributing/coding.html)
- Open a new GitHub [pull request](https://github.com/phpMv/ubiquity/blob/master/CONTRIBUTING.md#pull-requests) with your patch

### Writing guide and documentation
Read [Contributing to Ubiquity documentation](https://micro-framework.readthedocs.io/en/latest/contributing/doc.html)

### Coding of demo applications
See [Ubiquity demos](https://github.com/phpMv/ubiquity-demos)
### Writing a patch that fixes a bug
- Open a new GitHub [pull request](https://github.com/phpMv/ubiquity/blob/master/CONTRIBUTING.md#pull-requests) with the patch
- Ensure the PR description clearly describes the problem and solution. Include the relevant issue number if applicable
- Before submitting, please read the Contributing guide to know more about coding conventions and benchmarks

### Questions
Ask any question about how to use Ubiquity in the chat.

[![Join the chat at https://gitter.im/ubiquity-framework/community](https://badges.gitter.im/ubiquity-framework/community.svg)](https://gitter.im/ubiquity-framework/community?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Pull requests

The process described here has several objectives:

- Maintain Ubiquity's quality :
  - performances
  - code quality
  - ease of use
- Fix problems that are important to users
- Engage the community in working toward the best possible Ubiquity
- Enable a sustainable system for Ubiquity's maintainers to review contributions

Please follow these steps to have your contribution considered by the maintainers:

1. Follow the [contributing guide](https://micro-framework.readthedocs.io/en/latest/contributing/contributing-shem.html)
2. Create a Pull Request
3. After you submit your pull request, verify that all [status checks](https://help.github.com/articles/about-status-checks/) are passing <details><summary>What if the status checks are failing?</summary>If a status check is failing, and you believe that the failure is unrelated to your change, please leave a comment on the pull request explaining why you believe the failure is unrelated. A maintainer will re-run the status check for you. If we conclude that the failure was a false positive, then we will open an issue to track that problem with our status check suite.</details>
We especially will be interested in the following points:
   - passing tests
   - maintaining the coverage ratio
   - stability of code quality (scrutinizer code rating)
   - No degradation of performance for benchmarks performed 

While the prerequisites above must be satisfied prior to having your pull request reviewed, the reviewer(s) may ask you to complete additional design work, tests, or other changes before your pull request can be ultimately accepted.


Ubiquity's contributing process owes its inspiration to the [Atom contributing process](https://github.com/atom/atom/blob/master/CONTRIBUTING.md).
