Walker
======

A simple wrapper around Goutte to crawl an entire website and get some stats about each page :
- status,
- pages referring to it,
- other informations cactchable if you use run() method to implement your needs.

Walker get all "a href" values to build its crawling, so there is an extensions' exclusion mechanism to ignore elements which are not relevants, for example images.
See Parameters section below for more informations

## Usage :
In your composer.json, add Walker repository and its call into "require" block :

```
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
```

Run composer update :

```
php ./composer.phar update

```

Instanciate the crawler, start the crawl and output stats after the process :
```
$walker = new \Walker\Walker("http://www.somewebsite.fr");
$walker -> start();

echo "<pre>URL | STATUS | CALLED IN";
foreach($walker->getStats() as $stats){
    printf("\n%s | %s | %s",$stats[0], $stats[1], $stats[2]);
}
echo "</pre>";
```

If you want more informations or operations to be performed real-time during crawling you can pass an anonymous function to the run() method :

```
echo "<pre>URL | STATUS | CALLED IN | LAST MODIF";

$walker -> run(function ($client, $stats) {
    $lastMod = $client->getResponse()->getHeader("last-modified");
    printf("\n%s | %s | %s| %s",$stats[0], $stats[1], $stats[2], $lastMod);
    flush();
});
echo "</pre>";
```
## Parameters :
You can override configurations using setConfiguration() method, for example
```
$walker->setConfiguration("httpClientOptions",['curl.options' => array(
        CURLOPT_TIMEOUT      => 150
    )]
);
$walker->setConfiguration("excludedFileExt","`\.(jpg|jpeg|gif|png)$`i");
```
