[![Build Status](https://travis-ci.org/fpalomo/auto-pull-request-merger.png)](https://travis-ci.org/fpalomo/auto-pull-request-merger)

This simple script will parse every open pull request in a GitHub repository. It will check if the build is successful
and anybody has reviewed and approved the code.
If your company

This great Tool has been developed between several guys:
=====
Adrià Cidre, https://github.com/adriacidre
Natxo Treig, https://github.com/natxetee
and myself

USAGE:

php mergePullRequest.php \<GitHubUser\> \<GitHubPassword\> \<owner\> \<repo\>

all parameters can be set at Commands/Merge.php

WARNING: THE CURRENT VERSION IS STILL WORK IN PROGRESS . PLEASE , DOWNLOAD "STABLE" TAG FOR THE LATEST STABLE VERSION



How to extend functionalities
=====

You can define listener to system events at Listener\All.php

If your new classes have dependencies, please define this dependencies with a new file in "Dependency" directory
