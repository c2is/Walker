<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> Walker project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace Walker;


/**
 * A simple wrapper around Goutte to crawl a website
 *
 * @author André Cianfarani <a.cianfarani@c2is.fr>
 *
 * @api
 */
class Walker
{
    private $links;
    private $baseUrl;
    private $walkerClient;
    private $domainWildCard;
    private $subDomainsMask;
    private $invalidUrlsFound;

    public $configurations;
    public $storage;

    public function __construct($baseUrl, $subDomainsMask = null)
    {
        $this->storage = new Storage();
        $this->storage->addVarToStore("stats");
        $this->storage->addColumn("stats", "URL");
        $this->storage->addColumn("stats", "STATUS");
        $this->storage->addColumn("stats", "CALLED IN");

        $this->links  = array();
        $this->urlsVisited  = array();
        $this->invalidUrlsFound = array();
        $this->baseUrl = $baseUrl;
        if (strrpos($this->baseUrl, "/") == strlen($this->baseUrl)-1) {
            $this->baseUrl = substr($this->baseUrl, 0, strlen($this->baseUrl)-1);
        }
        $this->configurations = array();

        $this->setConfiguration("excludedFileExt", "`\.(jpg|jpeg|gif|png)$`i");
        $this->setConfiguration("forbiddenPattern", array("mailto", "#", "javascript"));
        $this->setConfiguration("httpClientOptions", ['curl.options' => array(
        CURLOPT_CONNECTTIMEOUT      => 150,
        CURLOPT_TIMEOUT      => 300,
        CURLOPT_CONNECTTIMEOUT_MS      => 150000,
        CURLOPT_LOW_SPEED_LIMIT      => 0,
        CURLOPT_LOW_SPEED_TIME      => 0
            )]
        );

        // get the end of domain : xx.xx.xxx.domaine.com will get .domain.com
        $domain = parse_url($this->baseUrl, PHP_URL_HOST);
        $domainWildCard = explode(".", $domain);
        $this->domainWildCard = ".".$domainWildCard[count($domainWildCard)-2].".".$domainWildCard[count($domainWildCard)-1];

        if ($subDomainsMask != null) {
            $this->subDomainsMask = $subDomainsMask;
        } else {
            $this->subDomainsMask = str_replace($this->domainWildCard, "", $domain);
        }

    }
    private function initClient()
    {
        $this->walkerClient  = new \Walker\Client();
        $this->walkerClient -> setClient(new \Guzzle\Http\Client('', $this->getConfiguration("httpClientOptions")));
        $this->walkerClient->setWalker($this);
        $this->walkerClient -> setMaxRedirects(10);
    }
    public function start($callback = null)
    {
        $this->initClient();
        $this->checkLinks($this->baseUrl, null, $callback);
    }
    public function run($callback = null)
    {
        $this->initClient();
        $this->checkLinks($this->baseUrl, null, $callback);
    }
    public function checkLinks($url, $referrer = "", $callback = null)
    {

        if (! $this->isUrlToCheck($url, $referrer)) {
            return true;
        }
        if ( ! $this->isValidUrl($url, $referrer)) {
            return true;
        }

        $this->walkerClient -> lastreferrer = $referrer;
        $crawler = $this->walkerClient->request('GET', $url);


        if (null !== $callback) {
            call_user_func($callback, $crawler, $this->walkerClient);
        }

        // getting  href attributes belonging to nodes of type "a"
        // Todo : deal or not with shortlink like Drupal ? Ex. : <link rel="shortlink" href="http://www.c2is.fr/node/25" />
        $nodes = $crawler->filterXPath('//a/@href');

        foreach ($nodes as $node) {
            $prefix = "";
            if (strpos($node->value, "http:") === false) {
                $prefix = $this->baseUrl;
                if (strpos($node->value, "/") !== 0) {
                    $prefix .= "/";
                }
            }

            $linkUri = $prefix.$node->value;

            if (! in_array($linkUri, $this->links)) {
                $this->links[] = $linkUri;
            }

            if ($this->isValidUrl($linkUri, $referrer)) {
                $this->checkLinks($linkUri, $url, $callback);
            }

        }
    }
    public function isUrlToCheck($url, $referrer)
    {
        $urlDomain = parse_url($url, PHP_URL_HOST);

        if (in_array($url, $this->urlsVisited)) {
            if ($referrer != "") {
                $this->storage->update("stats", "URL", $url, "CALLED IN", $referrer);
            }

            return false;
        }
        if (! $this->isValidUrl($url, $referrer)) {
            return false;
        }
        if ( ! preg_match("`".$this->subDomainsMask.$this->domainWildCard."`", $urlDomain) || preg_match($this->getConfiguration("excludedFileExt"), $url)) {
            return false;
        }

        return true;
    }
    public function isValidUrl($url, $referrer)
    {
        if (! is_string($url)) {
            return false;
        }
        foreach ($this->getConfiguration("forbiddenPattern") as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return false;
                break;
            }
        }
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            if ($this->storage->subArraySearch($this -> invalidUrlsFound, 0, $url) !== false) {
                $this->storage->updateSubArray($this -> invalidUrlsFound, 0, $url, 1, $referrer);
            } else {

                $this -> invalidUrlsFound[] = array($url, $referrer);

            }

            return false;
        }
        // filter_var considers http://www.portesdusoleil.commultipass-journee-hebergeur-adherent.html as an url
        // so we add this test to avoid malformatted url
        if ($url != $this->baseUrl) {
            if (preg_match("`".$this->subDomainsMask.$this->domainWildCard."`", $url)
                && ! preg_match("`".$this->subDomainsMask.$this->domainWildCard."/`", $url)) {

                    if (in_array($url, $this->urlsVisited)) {
                            $this->storage->updateSubArray($this -> invalidUrlsFound, 0, $url, 1, $referrer);
                    }

            return false;
            }
        }

        return true;
    }
    public function getInvalidUrlsFound()
    {
        return $this->invalidUrlsFound;
    }
    public function getLinks()
    {
        return $this->links;
    }
    public function setConfiguration($key,$value)
    {
        $this->configurations[$key] = $value;
    }
    public function getConfiguration($key)
    {
        return $this->configurations[$key];
    }
    public function showConfigurations()
    {
        var_export($this->configurations);
    }

}
