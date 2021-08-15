<?php

spl_autoload_register(function ($classes) {
    require_once 'classes/' . $classes . '.php';
});
