<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'firstname',
		'hobbies',
		'dob',
	];

	/**
	 * @var array
	 */
	protected $casts = [
		'dob' => 'date',
	];

}