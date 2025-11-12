<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuarios extends Model
{

    
    use HasFactory;

    protected $table = 'users';


    protected $fillable =
        [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token'
    ];


    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts =
        [
        'id' => 'string',
        'name' => 'string',
        'email' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'remember_token' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules =
    [
        'name' => 'required',
        'email' => 'required',
        'email_verified_at' => 'required',
        'password' => 'required',
        'remember_token' => 'required',
    ];


    /**
     * Custom messages for validation
     *
     * @var array
     */
    public static $messages =[

    ];


    /**
     * Accessor for relationships
     *
     * @var array
     */
    

}
