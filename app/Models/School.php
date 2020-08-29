<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
	protected $table = 'schools';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'name',
		'address',
		'year_established',
	];

	/**
	 * @var array
	 */
	protected $casts = [
		'year_established' => 'date',
	];

}