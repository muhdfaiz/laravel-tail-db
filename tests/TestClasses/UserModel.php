<?php

namespace Muhdfaiz\LaravelTailDb\Tests\TestClasses;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name', 'email'];
}