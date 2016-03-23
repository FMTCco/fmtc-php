<?php
namespace Fmtc;

use Illuminate\Database\Capsule\Manager as DB;

class Migration
{
	public function migrate()
	{
		DB::transaction(function () {
		    $this->createCategoriesTable();
		    $this->createTypesTable();
		    $this->createDealsTable();
		    $this->createDealsCategoriesTable();
		    $this->createDealsTypesTable();
		    $this->createMerchantsTable();
		    $this->createNetworksTable();
		});
	}

	protected function createCategoriesTable()
	{
		if (! DB::schema()->hasTable('categories')) {
			DB::schema()->create('categories', function($table)
			{
				$table->increments('id');
				$table->string('cSlug', 27)->unique();
				$table->string('cName', 29);
				$table->integer('nParentID')->unsigned();
				$table->boolean('bHoliday')->index();
				$table->string('cParent', 18);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			});
		}
	}

	protected function createTypesTable()
	{
		if (! DB::schema()->hasTable('types')) {
			DB::schema()->create('types', function($table) {
				$table->increments('id');
				$table->string('cSlug', 27)->unique();
				$table->string('cName', 29);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			});
		}
	}

	protected function createDealsTable()
	{
		if (! DB::schema()->hasTable('deals')) {
			DB::schema()->create('deals', function($table) {
				$table->increments('id');
				$table->integer('nCouponID')->index();
				$table->string('cMerchant', 255);
				$table->integer('nMerchantID')->index();
				$table->integer('nMasterMerchantID');
				$table->string('cNetwork', 3);
				$table->string('cStatus', 7);
				$table->string('cLabel', 250);
				$table->string('cImage', 88);
				$table->string('cRestrictions', 100);
				$table->string('cCode', 8);
				$table->string('dtStartDate', 25);
				$table->string('dtEndDate', 25);
				$table->string('cLastUpdated', 25);
				$table->string('cCreated', 25);
				$table->text('cAffiliateURL');
				$table->text('cDirectURL');
				$table->text('cSkimlinksURL');
				$table->text('cFMTCURL');
				$table->text('cPixelHTML');
				$table->decimal('fSalePrice', 6, 2);
				$table->decimal('fWasPrice', 6, 2);
				$table->decimal('fDiscount', 5, 2);
				$table->integer('nPercent');
				$table->decimal('fThreshold', 4, 2);
				$table->decimal('fRating', 5, 2);
				$table->string('aBrands');
				$table->string('aLocal');
				$table->boolean('bStarred', 1);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			});
		}
	}

	protected function createDealsCategoriesTable()
	{
		if (! DB::schema()->hasTable('deals_categories')) {
			DB::schema()->create('deals_categories', function($table) {
				$table->increments('id');
				$table->integer('nCouponID');
				$table->string('cCategorySlug', 50);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
				$table->index('nCouponID','cCategorySlug');
			});
		}
	}

	protected function createDealsTypesTable()
	{
		if (! DB::schema()->hasTable('deals_types')) {
			DB::schema()->create('deals_types', function($table) {
				$table->increments('id');
				$table->integer('nCouponID');
				$table->string('cTypeSlug', 50);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
				$table->index('nCouponID','cCategorySlug');
			});
		}
	}

	protected function createMerchantsTable()
	{
		if (! DB::schema()->hasTable('merchants')) {
			DB::schema()->create('merchants', function($table) {
				$table->increments('id');
				$table->integer('nMerchantID')->index();
				$table->integer('nMasterMerchantID');
				$table->integer('nSkimlinksID');
				$table->string('cName', 20);
				$table->string('cNetwork', 3);
				$table->integer('cProgramID');
				$table->string('cNetworkNotes', 32);
				$table->integer('bDual');
				$table->string('aDualMerchants');
				$table->integer('nParentMerchantID');
				$table->text('cAffiliateURL');
				$table->text('cSkimlinksURL');
				$table->text('cFMTCURL');
				$table->string('cHomepageURL', 255);
				$table->string('cStatus', 6);
				$table->string('dtCreated', 25);
				$table->string('dtLastUpdated', 25);
				$table->integer('bSelected');
				$table->integer('bRelationshipExists');
				$table->string('cPrimaryCountry', 2);
				$table->string('aShipToCountries');
				$table->integer('bAPOFPO');
				$table->string('cPrimaryCategory', 20);
				$table->string('aCategories');
				$table->integer('bMobileCertified');
				$table->text('aLogos');
				$table->string('aPaymentOptions');
				$table->string('cCustomMerchantLogo', 10);
				$table->string('cCustomMerchantDescription', 10);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			});
		}
	}

	public function createNetworksTable()
	{
		if (! DB::schema()->hasTable('networks')) {
			DB::schema()->create('networks', function($table) {
				$table->increments('id');
				$table->string('cName');
				$table->string('cSlug', 5);
				$table->string('aCountries');
			});
		}
	}

	public function rollbackMigration()
	{
		DB::schema()->dropIfExists('deals');
		DB::schema()->dropIfExists('categories');
		DB::schema()->dropIfExists('types');
		DB::schema()->dropIfExists('merchants');
		DB::schema()->dropIfExists('deals_types');
		DB::schema()->dropIfExists('deals_categories');
		DB::schema()->dropIfExists('networks');
	}
}
