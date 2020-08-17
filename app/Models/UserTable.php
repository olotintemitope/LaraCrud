<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTable extends Model
{
	protected $table = 'usertables';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'firstname',
		'lastname',
		'dob',
	];

	/**
	 * @var array
	 */
	protected $casts = [
		'dob' => 'date',
	];

}