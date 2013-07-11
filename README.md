Walker
======

A simple wrapper around Goutte to crawl an entire website and get some stats about each page :
- status,
- pages referring to it,
- other informations cactchable if you use run() method to implement your needs.

Walker get all "a href" values to build its crawling, so there is an extensions' exclusion mechanism to ignore elements which are not relevants, for example images.
See Parameters section below for more informations.

By default crawling is bound to subdomain given, but the second parameter of constructor allow you to define which other subdomains could be crawled. A regexp defines allowed subdomains, example which allow any subdomains : 
```
$walker = new \Walker\Walker("http://www.somewebsite.fr", ".*");
```

## Usage :
In your composer.json add Walker into "require" block :

```
{
    "require": {
        "c2is/walker" : "dev-master"
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
echo "<pre>".implode(" | ", $walker->storage->getColumns("stats"));
foreach($walker->storage->get("stats") as $stats){
    printf("\n%s | %s | %s",$stats["URL"], $stats["STATUS"], $stats["CALLED IN"]);
}
echo "</pre>";
```

If you want more informations or operations to be performed real-time during crawling you can pass an anonymous function to the run() method :

```
echo "<pre>".implode(" | ", $walker->storage->getColumns("stats"))." | LAST MODIF";
$walker -> run(function ($crawler, $client) {
    $lastMod = $client->getResponse()->getHeader("last-modified");
    $stats = $client->getStats();
    printf("\n%s | %s | %s| %s",$stats["URL"], $stats["STATUS"], $stats["CALLED IN"], $lastMod);
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
