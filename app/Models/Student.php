<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
	/**
	 * @var array
	 */
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
		'isAdmitted',
	];
	/**
	 * @var array
	 */
	protected $casts = [
		'dob' => 'date',
		'isAdmitted' => 'bool'
	];
}