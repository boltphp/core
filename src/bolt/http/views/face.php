<?php

namespace bolt\http\views;

interface face {

    public function __invoke();

    public function render();

    public function __toString();

}