## FMTC PHP SDK
This is the official php package to interface with FMTC's API.

### Features
* Migrate the database schema
* Fetch FMTC's feeds (json): deals, categories, types, merchants and networks
* Process full and incremental feeds and store in database
* Retreive records from the database with an easy to use API

**Installation**
```php
composer require fmtc/fmtc-php
```

**Requirements**
* PHP >5.4
* You may need to increase timeout so the larger API calls don't fail.
```php
ini_set('default_socket_timeout', 6000);
```

**Initialization**
```php
$fmtc = new Fmtc([
	'api_key' => [api_key],
	'database' => [database],
	'host' => [host],
	'username' => [username],
	'password' => [password]
]);
```

**Fetch JSON Feeds**
```php
// Note: these methods return raw json

// Deal Feed
$fmtc->dealFeed()->fetchFull();
$fmtc->dealFeed()->fetchIncremental();

// Merchant Feed
$fmtc->merchantFeed()->fetchFull();
$fmtc->merchantFeed()->fetchIncremental();

// Category Feed
$fmtc->categoryFeed()->fetchFull();

// Type Feed
$fmtc->typeFeed()->fetchFull();

// Network Feed
$fmtc->networkFeed()->fetchFull();
```

**Migrating the Database**
```php
// migrate the database
$fmtc->database()->migrate();

// rollback the migration
$fmtc->database()->rollbackMigration();
```

**Processing Feeds**
```php
// Note: these methods pull down the JSON, parse it, normalize it, and store it in the database.

// Deal Feed
$fmtc->dealFeed()->processFull();
$fmtc->dealFeed()->processIncremental();

// Merchant Feed
$fmtc->merchantFeed()->processFull();
$fmtc->merchantFeed()->processIncremental();

// Category Feed
$fmtc->categoryFeed()->processFull();

// Type Feed
$fmtc->typeFeed()->processFull();

// Network Feed
$fmtc->networkFeed()->processFull();
```

**Retrieving Records**
These are methods to retreive records from the database. 
The methods return single objects or arrays of objects.
The results are sorted by rating by default.
```php
// Deals
$fmtc->deals()->get($dealId);
$fmtc->deals()->all([$limit, $offset]);
$fmtc->deals()->getByCategorySlug($categorySlug, [$limit, $offset]);
$fmtc->deals()->getByTypeSlug($typeSlug, [$limit, $offset]);
$fmtc->deals()->getByMerchant($merchantId, [$limit, $offset]);
$fmtc->deals()->getByMasterMerchant($masterMerchantId, [$limit, $offset]);
$fmtc->deals()->getBySearch($searchString, [$limit, $offset]);

// Merchants
$fmtc->merchants()->get($merchantId);
$fmtc->merchants()->all();
$fmtc->merchants()->getByMasterMerchant($masterMerchantId);
$fmtc->merchants()->getBySearch($searchString);

// Categories
$fmtc->categories()->get($categorySlug);
$fmtc->categories()->all();
$fmtc->categories()->getByParent($categoryParentSlug);
$fmtc->categories()->getBySearch($searchString);

// Types
$fmtc->types()->get($typeSlug);
$fmtc->types()->all();
$fmtc->types()->getBySearch($searchString);

// Networks
$fmtc->networks()->get($networkSlug);
$fmtc->networks()->all();
$fmtc->networks()->getBySearch($searchString);

// Custom Fmtc Url
$fmtc->api()->fetchUrl($url, [$options]);
```

