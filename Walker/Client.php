<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> Walker project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace Walker;

use Goutte\Client as BaseClient;
use Symfony\Component\BrowserKit\Response;

class Client extends BaseClient
{
    private $walker;
    public $lastReferer;
    private $lastStatus;
    private $lastUri;
    public function doRequest($request)
    {
        $uri = $request->getUri();
        if ($this->walker->isUrlToCheck($uri, "")) {
            $response = parent::doRequest($request);
            $statusCode = $response->getStatus();

            $this->lastUri = $uri;
            $this->lastStatus = $statusCode;
            $this->walker->storage->add("stats", array("URL"=>$uri,"STATUS"=>$statusCode,"CALLED IN"=>$this->lastReferer));
        } else {
            $headers[] = "";
            $statusCode = $this->walker->storage->find("stats", $uri)[1];
            $response = new Response("", $statusCode, $headers);
        }

        $this->walker->urlsVisited[] = $uri;

        return $response;

    }
    public function setWalker(Walker $walker)
    {
        $this->walker = $walker;
    }
    public function getStats()
    {
        return array("URL"=>$this->lastUri, "STATUS"=>$this->lastStatus, "CALLED IN"=>$this->lastReferer);
    }

}
