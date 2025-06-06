--TEST--
Hooks work on backed readonly properties
--FILE--
<?php

interface DbConnection {
   public function loadCategory(string $id): Category;
}

class Category {
   public function __construct(public string $name) {}
}

class MockDbConnection implements DbConnection {
   public function loadCategory(string $id): Category {
       echo "hit database\n";
       return new Category("Category {$id}");
   }
}

readonly class LazyProduct
{
   private DbConnection $dbApi;
   private string $categoryId;

   public Category $category {
       get {
           return $this->category ??= $this->dbApi->loadCategory($this->categoryId);
       }
   }
}

$product = new LazyProduct();
$reflect = new ReflectionClass($product);

$db = $reflect->getProperty('dbApi');
$db->setAccessible(true);
$db->setValue($product, new MockDbConnection());

$categoryId = $reflect->getProperty('categoryId');
$categoryId->setAccessible(true);
$categoryId->setValue($product, '42');

// lazy loading, hit db
$category1 = $product->category;
echo $category1->name . "\n";

// cached value
$category2 = $product->category;
echo $category2->name . "\n";

// Verify same instance is returned (backing field caching works)
var_dump($category1 === $category2);

?>
--EXPECT--
hit database
Category 42
Category 42
bool(true)
