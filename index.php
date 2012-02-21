<?php
require 'Slim/Slim.php';
require 'gilt_api.php';

$api_key = 'c73a7c168dd90eb31a76e2e9a6290890';
$gilt = new Gilt($api_key);

$partial = new Slim_View();
$partial->setTemplatesDirectory('partials');

function renderPartial($template, $data) {
  global $partial;
  $partial->setData($data);
  return $partial->render($template);
}

$app = new Slim();

// JSON
$app->get('/sales/upcoming.json', 'sales_upcoming_json');
$app->get('/sales/active.json', 'sales_active_json');
$app->get('/sales/:store_key/upcoming.json', 'store_upcoming_json');
$app->get('/sales/:store_key/active.json', 'store_active_json');
$app->get('/sales/:store_key/:sale_key/detail.json', 'sale_detail_json');
$app->get('/product/:product_key/detail.json', 'product_detail_json');

// HTML
$app->get('/', 'home');
$app->get('/sales', 'home');
$app->get('/sales/:store_key', 'store');
$app->get('/sales/:store_key/:sale_key', 'sale');
$app->get('/product/:product_key', 'product');

function home() {
  global $app, $gilt;
  $sales = $gilt->getActiveSales();
  $stores = $sales->getStores();
  $data = array(
    'base_url' => $app->request()->getRootUri() . '/',
    'heading' => 'Hello Shoppers!', 
    'detail' => 'Get busy!',
    'stores' => $stores
  );
  $data['hero'] = renderPartial('hero.php', $data);
  $data['content'] = renderPartial('home.php', $data);
  $app->render('gilt.php', $data);
}

function store($store_key) {
  global $app, $gilt;
  if (!$gilt->validateStore($store_key)) {
    $app->notFound();
  }
  $store = $gilt->getActiveSales($store_key);
  $data = array(
    'base_url' => $app->request()->getRootUri() . '/',
    'heading' => $store_key,
    'detail' => 'Get busy!',
    'store_key' => $store_key,
    'store' => $store
  );
  $data['hero'] = renderPartial('hero.php', $data);
  $data['content'] = renderPartial('store.php', $data);
  $app->render('gilt.php', $data);
}

function sale($store_key, $sale_key) {
  global $app, $gilt;
  if (!$gilt->validateStore($store_key)) {
    $app->notFound();
  }
  $sale = $gilt->getSale($store_key, $sale_key);
  $imageUrls = $sale->getImageUrls();
  $products = array();
  foreach ($sale->getProducts() as $product_id) {
    $detail = $gilt->getProduct($product_id);
    $detail->fixProduct($product_id); // hack to fix broken urls in product details
    $products[] = $detail;
  }
  $data = array(
    'base_url' => $app->request()->getRootUri() . '/',
    'heading' => $sale->getName(),
    'detail' => $sale->getDescription(),
    'image_url' => $imageUrls['300x280']->getUrl(),
    'sale' => $sale,
    'products' => $products
  );
  $data['hero'] = renderPartial('hero.php', $data);
  $data['content'] = renderPartial('sale.php', $data);
  $app->render('gilt.php', $data);
}

function product($product_key) {
  global $app, $gilt;
  $product = $gilt->getProduct($product_key);
  $imageUrls = $product->getImageUrls();
  $data = array(
    'base_url' => $app->request()->getRootUri() . '/',
    'heading' => $product->getName(),
    'detail' => $product->getDescription(),
    'image_url' => $imageUrls['300x400']->getUrl(),
    'product' => $product
  );
  $data['hero'] = renderPartial('hero.php', $data);
  $data['content'] = '';
  $app->render('gilt.php', $data);
}

function sales_upcoming_json() {
  global $app, $gilt;
  $sales = $gilt->getUpcomingSales();
  $app->render('json.php', array('json' => $sales->getJson()));
}

function store_upcoming_json($store_key) {
  global $app, $gilt;
  if (!$gilt->validateStore($store_key)) {
    $app->notFound();
  }
  $sales = $gilt->getUpcomingSales($store_key);
  $app->render('json.php', array('json' => $sales->getJson()));
}

function sales_active_json() {
  global $app, $gilt;
  $sales = $gilt->getActiveSales();
  $app->render('json.php', array('json' => $sales->getJson()));
}

function store_active_json($store_key) {
  global $app, $gilt;
  if (!$gilt->validateStore($store_key)) {
    $app->notFound();
  }
  $sales = $gilt->getActiveSales($store_key);
  $app->render('json.php', array('json' => $sales->getJson()));
}

function sale_detail_json($store_key, $sale_key) {
  global $app, $gilt;
  if (!$gilt->validateStore($store_key)) {
    $app->notFound();
  }
  $sale = $gilt->getSale($store_key, $sale_key);
  $app->render('json.php', array('json' => $sale->getJson()));
}

function product_detail_json($product_key) {
  global $app, $gilt;
  $product = $gilt->getProduct($product_key);
  $app->render('json.php', array('product' => $product));
}

$app->run();

