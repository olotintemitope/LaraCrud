<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class User extends Model
{
	/**
	 * @var array
	 */
	protected $table = 'users';
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
		'age',
	];
	/**
	 * @var array
	 */
	protected $casts = [
		'dob' => 'date',
		'isAdmitted' => 'bool'
	];
}