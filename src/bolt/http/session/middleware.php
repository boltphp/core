<?php

namespace bolt\http\session;
use \b;

class middleware extends \bolt\http\middleware {

    public function before() {
        if (!$this->http->pluginExists('session')) {return;}

        $name = $this->http['session']->getName();

        if (!$this->request->cookies->has($name)) {return;}

        $id = $this->request->cookies->get($name);

        $this->http['session']->setId($id);
        $this->http['session']->start();

    }

    public function after() {
    	$this->http['session']->save();
    }

}