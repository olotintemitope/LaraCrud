<?php

namespace Laztopaz\Models;

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
		'admitted_at',
	];

	/**
	 * @var array
	 */
	protected $casts = [
		'dob' => 'date',
		'admitted_at' => 'datetime',
	];

}