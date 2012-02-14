<?php

require_once 'rest_curl_client.php';

class RestApi {
  
  private $base_url;
  private $base_params;

  public function __construct($base_url, $base_params) {
    $this->base_url = $base_url;
    $this->base_params = $base_params;
  }

  public function get($path_els = array(), $params = array()) {
    $url = $this->buildUrl($path_els, $params);
    $rest = new RestCurlClient();
    $rsp = $rest->get($url);
    return $rsp;
  }

  function buildUrl($path_els, $params) {
    $path = '';
    foreach ($path_els as $el) {
      if (empty($el)) continue;
      $path = (empty($path)) ? '' : ($path . '/');
      $path = $path . $el;
    }
    $params = array_merge($this->base_params, $params);
    $query = '';
    foreach ($params as $key => $value) {
      $query = (empty($query)) ? '?' : ($query . '&');
      $query = $query . $key . '=' . urlencode($value);
    }
    $url = $this->base_url . $path . $query;
    return $url;
  }
}