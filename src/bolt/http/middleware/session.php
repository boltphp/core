<?php

namespace bolt\http\middleware;
use \b;

class session extends \bolt\http\middleware {

    public function before() {
        if (!$this->http->pluginExists('session')) {return;}

        $name = $this->http['session']->getName();

        if (!$this->request->cookies->has($name)) {return;}

        $this->http['session']->start();

    }

}