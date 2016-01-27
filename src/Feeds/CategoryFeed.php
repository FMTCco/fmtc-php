<?php
namespace Fmtc\Feeds;

use Illuminate\Database\Capsule\Manager as DB;

class CategoryFeed extends Feed
{
	protected $url = 'http://services.fmtc.co/v2/getCategories';

	protected function storeFull($json)
	{	
		$categories = json_decode($json, true);

		// grab categories from the database
		$dbCategories = collect(DB::table('categories')->get(['cSlug']))->keyBy('cSlug')->toArray();

		// grab an array of columns in the categories table
		$columns = DB::select('select COLUMN_NAME as `column` from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'fmtc_categories\'');

		// set the counters for reporting
		$insertCount = 0;
		$removeCount = 0;

		// walk through the categories from a merchant feed
		$jsonCategoryIds = [];
		foreach($categories as $category) {
			// is the category missing from the database?
			if (! isset($dbCategories[$category['cSlug']])) {
				// insert it (this is faster than building an insert queue and bulk inserting)
				DB::table('categories')->insert($this->formatForInsertion($category, $columns));
				$insertCount++;
			}

			// collect an array of ids to aid in the remove	queue
			$jsonCategoryIds[] = $category['cSlug'];
		}

		// remove old categories showing up in the database but not in the new merchant feed.
		$removeQueue = array_diff(array_keys($dbCategories), $jsonCategoryIds);
		$removeCount = count($removeQueue);
		foreach ($removeQueue as $categoryId) {
			DB::table('categories')->where('cSlug', $categoryId)->delete();
		}
		//---- debugging
		// debug($removeCount . ' removed');
		// debug($insertCount . ' inserted');
		//----- 
		
		return true;
	}

	public function processIncremental()
	{
		return false;
	}
}