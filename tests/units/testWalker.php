<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> checkLampConf project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */


namespace tests\units;

require_once 'vendor/bin/atoum';

require_once __DIR__ . '/../../vendor/autoload.php';

use \mageekguy\atoum;
use \Walker;

class Client extends atoum\test
{
    public function test__construct()
    {
        $this->string("Hello World!")->isEqualTo('Hello World!');
    }
}