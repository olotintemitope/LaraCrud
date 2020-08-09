<?php 
namespace App\Entities; 
use Illuminate\Database\Eloquent\Model;
class Supporter extends Model 
{	/**
 	* @var string
 	*/	protected $table = 'supporters';
	/**
 	* The attributes that are mass assignable.
 	*
 	* @var array
 	*/	protected $fillable = [		'firstname',		'lastname',		'city',		'country',		'zipcode',	];
	/**
 	* @var array
 	*/	protected $casts = [
	];
}