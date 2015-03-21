<?php namespace App\validators;

use \Illuminate\Support\ServiceProvider;
//use App\validators\CustomValidators;

class ValidationServiceProvider extends ServiceProvider {

  public function register(){}

  public function boot()
  {
    $this->app->validator->resolver(function($translator, $data, $rules, $messages)
    {
      return new CustomValidator($translator, $data, $rules, $messages);
    });
  }

}
