<?php
namespace Fmtc; 

use Illuminate\Database\Capsule\Manager as DB;

class Categories
{
	public function get($slug)
	{
		$category = DB::table('categories')
						->where('cSlug', $slug)
						->first();

		return ($category) ? $category : false;
	}

	public function getByID($id)
	{
		$category = DB::table('categories')
						->where('id', $id)
						->first();

		return ($category) ? $category : false;
	}

	public function all()
	{
		$categories = DB::table('categories')->get();

		return $categories;
	}

	public function getByParent($parentSlug)
	{
		$categories = DB::table('categories')
						->where('cParent', $parentSlug)
						->get();

		return $categories;
	}

	public function getBySearch($search)
	{
		$categories = DB::table('categories')
						->where(function($query) use ($search) {
							$search = '%' . $search . '%';
							$query->where('cSlug', 'like', $search)
								  ->orWhere('cParent', 'like', $search)
								  ->orWhere('cName', 'like', $search);
						})
						->get();

		return $categories;
	}
}