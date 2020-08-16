<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
	protected $table = 'students';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'firstname',
		'lastname',
		'dob',
		'is_admitted',
		'gender',
		'timezone',
	];

	/**
	 * @var array
	 */
	protected $casts = [
		'dob' => 'date',
		'is_admitted' => 'bool',
	];

}