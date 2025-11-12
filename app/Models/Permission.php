<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{

    
    use HasFactory;

    protected $table = 'permissions';


    protected $fillable =
        [
        'name',
        'subject',
        'guard_name'
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
        'subject' => 'string',
        'guard_name' => 'string',
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
        'subject' => 'required',
        'guard_name' => 'required',
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
