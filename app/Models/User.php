<?php

namespace app\Models;

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
		'lastname',
		'age',
		'dob',
	];

	/**
	 * @var array
	 */
	protected $casts = [
		'dob' => 'date',
	];

}