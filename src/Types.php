<?php
namespace Fmtc; 

use Illuminate\Database\Capsule\Manager as DB;

class Types
{
	public function get($slug)
	{
		$type = DB::table('types')
						->where('cSlug', $slug)
						->first();

		return ($type) ? $type : false;
	}

	public function all()
	{
		$types = DB::table('types')->get();

		return $types;
	}

	public function getBySearch($search)
	{
		$types = DB::table('types')
						->where(function($query) use ($search) {
							$search = '%' . $search . '%';
							$query->where('cSlug', 'like', $search)
								  ->orWhere('cName', 'like', $search);
						})
						->get();

		return $types;
	}
}