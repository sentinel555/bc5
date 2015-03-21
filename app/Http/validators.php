<?php

Validator::extend('alpha_spaces', function($attribute, $value)
{
    return preg_match('/^[\pL\pN\s_-]+$/u', $value);
});

