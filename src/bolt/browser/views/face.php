<?php

namespace bolt\browser\views;

interface face {

    public function __invoke();

    public function render();

    public function __toString();

}