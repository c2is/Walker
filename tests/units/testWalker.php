<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> checkLampConf project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */


namespace Walker\tests\units;

require_once 'vendor/bin/atoum';

require_once __DIR__ . '/../../vendor/autoload.php';

use \mageekguy\atoum;
use \Walker;

class Client extends atoum\test
{
    public function testgetStats()
    {
        $client = new \Walker\Client();
        $this->array($client->getStats())->hasKey("URL");
    }
}