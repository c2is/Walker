Walker
======

A simple wrapper around Goutte to crawl a website

## Usage :
In your composer.json, add Walker repository and call it into "require" block :


{

    "repositories": [
        {"type":"vcs", "url":"git@github.com:c2is/Walker.git"}
    ],
    "require": {
        "c2is/Walker" : "dev-master"
    },
    "minimum-stability": "dev",
    "autoload": {
        "psr-0": {
            "": "src/"
        }
    },
}