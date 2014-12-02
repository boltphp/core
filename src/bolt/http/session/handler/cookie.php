<?php

namespace bolt\http\session\handler;
use \b;

class cookie implements \SessionHandlerInterface {

    private $_config = [];

    protected $manager;
    protected $http;

    protected $lifetime;
    protected $domain;
    protected $secret;

    public function __construct($http, $config = []) {
        $this->_config = $config;
        $this->http = $http;

        $this->lifetime = b::param('lifetime', '+1 day', $config);
        $this->domain = b::param('domain', false, $config);
        $this->secret = substr(b::param('secret', false, $config), 0, 24);

    }

    public function setManager(\bolt\http\session $manager) {
        $this->manager = $manager;
        $this->http = $manager->getHttp();
        return $this;
    }

    public function open($path, $name) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $req = $this->http->getRequest();

        $data = $req->cookies->get($this->manager->getName()) ?: false;
        if ($data) {
            return $this->decodeData(base64_decode($data));
        }
        return '';
    }

    public function encodeData($data) {
        $data = json_encode($data);

        if (!$this->secret) {return $data; }

        $key = $this->secret;

        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
        mcrypt_generic_init($td, $key, $iv);
        $encrypted_data = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $encrypted_data;
    }

    public function decodeData($data) {
        if (!$this->secret) { return json_decode($data, true); }

        $key = $this->secret;

        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
        mcrypt_generic_init($td, $key, $iv);
        $decrypted_data = mdecrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return json_decode($decrypted_data, true);

    }

    public function write($id, $data) {
        if(!$this->manager->isStarted()) {return;}

        $this->http->response->setCookie([
                'name' => $this->manager->getName(),
                'value' => base64_encode($this->encodeData($data)),
                'expire' => strtotime($this->lifetime),
                'domain' => $this->domain
            ]);

        return $this;
    }

    public function destroy($id) {
        $this->http->response->setCookie([
                'name' => $this->manager->getName(),
                'value' => false,
                'expire' => time()+1,
                'domain' => $this->domain
            ]);
    }

    public function gc($max) {
        return true;
    }


}