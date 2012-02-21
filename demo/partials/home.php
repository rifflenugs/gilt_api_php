<?php 
$stores = $this->data['stores'];
foreach ($stores as $store_key => $store) {
  $store_url = 'sales/' . $store_key;
?>
<div class="item">
  <h2><a href="<?php echo $store_url; ?>"><?php echo $store_key; ?></a></h2>
<?php
  $count = 0;
  foreach ($store as $sale) {
    $count = $count + 1;
    if ($count > 5) {
      $remaining = count($store) - $count;
      if ($remaining > 0) {
?>
    and <a href="<?php echo $store_url; ?>"><?php echo $remaining . ' more sale' . (($remaining > 0) ? 's' : ''); ?></a>
<?php
      }
      break;
    }
    $url = substr($sale->getSale(), strlen(Gilt::BASE_URL_V1));
    $url = preg_replace('#/\w*\.json#', '', $url);
?>
    <a href="<?php echo $url; ?>"><?php echo $sale->getName(); ?></a><br/>
<?php
  }
?>
</div>
<?php
}
