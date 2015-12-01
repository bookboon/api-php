<?php
namespace Bookboon\Api;
/*
 *  Copyright 2012 Bookboon.com Ltd.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 * 
 */

use Exception;

if (!class_exists('Memcached')) {
   throw new Exception('Bookboon_Memcached requires the memcached PHP extension');
}

class Memcached implements Cache {

   private $server = 'localhost';
   private $port = 11211;
   private $ttl = 600;
   private $cache = null;

   function __construct() {
      $this->cache = new Memcached();

      if (!$this->cache->addServer($this->server, $this->port)) {
         $this->cache = null;
      }
   }
   
   function get($key) {
      $data = $this->cache->get($key);
      return false === $data ? null : $data;
   }

   function save($key, $data) {
      return $this->cache->set($key, $data, $this->ttl);
   }

   function delete($key) {
      return $this->cache->delete($key);
   }

}

?>