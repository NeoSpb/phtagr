<?php
/*
 * phtagr.
 * 
 * Multi-user image gallery.
 * 
 * Copyright (C) 2006-2008 Sebastian Felis, sebastian@phtagr.org
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2 of the 
 * License.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class QueryHelper extends AppHelper {
  var $helpers = array('html'); 

  /** Skip specific query parameters */
  var $_excludePage = array('prevPage' => true, 
                    'nextPage' => true,
                    'prevImage' => true, 
                    'nextImage' => true, 
                    'count' => true, 
                    'pages' => true, 
                    'page' => 1, 
                    'show' => 12,
                    'pos' => 1, 
                    'image' => true,
                    'video' => true
                    );

  var $_excludeImage = array('prevImage' => true,
                    'nextImage' => true,
                    'count' => true,
                    'pages' => true,
                    'page' => 1,
                    'show' => 12,
                    'pos' => 1,
                    'image' => true,
                    'videw' => true
                    );

  var $_query = array(); 

  /** Initialize query parameters from the global parameter array, which is
   * set by the query component */
  function initialize() {
    if (!isset($this->params['query']))
      return;
    $this->_query = $this->params['query'];
  }

  /** 
    @param query Optional query array
    @return Array of current query options with the syntax of
    name:value[,value...]
   */
  function _buildParams($query = null, $exclude = null) {
    if ($query == null)
      $query = &$this->_query;
    if ($exclude == null)
      $exclude = &$this->_excludePage;

    $params = array();
    foreach ($query as $name => $value) {
      if (isset($exclude[$name]) && 
        ($exclude[$name] === true || $exclude[$name] == $value))
        continue;
      if (is_array($value)) {
        // arrays like tags, categories, locations
        if (count($value)) {
          $params[] = $name.':'.implode(',', $value);
        }
      } else {
        $params[] = $name.':'.$value;
      }
    }
    return $params;
  }

  /** Returns all parameters of the current query */
  function getQuery() {
    return $this->_query;
  }

  function setQuery($query) {
    $this->_query = $query;
  }

  /** Clear all parameter values of the query */
  function clear() {
    $this->_query = array();
  }

  /** Set the current parameter with the given value. This function will
   * overwrite the existing value(s)
   * @param name Parameter name 
   * @param value Parameter value */
  function set($name, $value) {
    $this->_query[$name] = $value;    
  }

  /** Returns a specific parameter by name
    @param name Parameter name
    @param default Default value, if parameter is not set. Default is null
    @return Parameter value */
  function get($name, $default = null) {
    if (isset($this->_query[$name]))
      return $this->_query[$name];
    else
      return $default;
  }

  /** Adds the value to the current parameter name. If the parameter is not an
   * array, it converts it to an array and adds the values to the stack 
   * @param name Parameter name 
   * @param value Parameter value */
  function add($name, $value) {
    if (!isset($this->_query[$name]))
      $this->_query[$name] = array();
    
    if (!is_array($this->_query[$name])) {
      $value = $this->_query[$name];
      $this->_query[$name] = array($value);
    }
       
    if (!in_array($value, $this->_query[$name]))
      array_push($this->_query[$name], $value);
  }

  /** Removes a parameter value of the parameter name. If more than one value
   * is stacked to the parameter value, is removes only the given value.
   * @param name Parameter name 
   * @param value Parameter value */
  function remove($name, $value = null) {
    if (!isset($this->_query[$name]))
      return;

    if (!is_array($this->_query[$name])) {
      unset($this->_query[$name]);
    } elseif ($value !== null) {
      $key = array_search($value, $this->_query[$name]);
      if ($key !== false)
        unset($this->_query[$name][$key]);

      // Removes parameter if no value exists
      if (!count($this->_query[$name]))
        unset($this->_query[$name]);
    }
  }

  function getParams($query = null, $exclude = null) {
    return implode('/', $this->_buildParams($query, $exclude));
  }

  /** @param query Optional query array
    @return uri of current query */
  function getUri($query = null, $exclude = null) {
    $params = $this->_buildParams($query, $exclude);
    return '/'.$this->params['controller'].'/query/'.implode('/', $params);
  }

  function prev() {
    if (!isset($this->params['query']))
      return;
    $query = $this->params['query'];
    $exclude = am($this->_excludePage, array('pos' => true));
    if ($query['prevPage']) {
      $query['page']--;
      return $this->html->link('prev', $this->getUri($query, $exclude));
    }
  }
  
  function numbers() {
    if (!isset($this->params['query']))
      return;
    $output = '';
    $query = $this->params['query'];
    $exclude = am($this->_excludePage, array('pos' => true));
    if ($query['pages'] > 1) {
      $count = $query['pages'];
      $current = $query['page'];
      for ($i = 1; $i <= $count; $i++) {
        if ($i == $current) {
          $output .= " <span class=\"current\">$i</span> ";
        }
        else if ($count <= 12 ||
            ($i < 3 || $i > $count-2 ||
            ($i-$current < 4 && $current-$i<4))) {
          $query['page']=$i;
          $output .= ' '.$this->html->link($i, $this->getUri($query, $exclude));
        }
        else if ($i == $count-2 || $i == 3) {
          $output .= " ... ";
        }
      }
    }
    return $output;
  }

  function next() {
    if (!isset($this->params['query']))
      return;
    $query = $this->params['query'];
    $exclude = am($this->_excludePage, array('pos' => true));
    if ($query['nextPage']) {
      $query['page']++;
      return $this->html->link('next', $this->getUri($query, $exclude));
    }
  }

  function prevImage() {
    if (!isset($this->params['query']))
      return;
    $query = $this->params['query'];
    if (isset($query['prevImage'])) {
      $query['pos']--;
      $query['page'] = ceil($query['pos'] / $query['show']);
      return $this->html->link('prev', '/explorer/image/'.$query['prevImage'].'/'.$this->getParams($query, $this->_excludeImage));
    }
  }

  function up() {
    if (!isset($this->params['query']))
      return;
    $query = $this->params['query'];
    $query['page'] = ceil($query['pos'] / $query['show']);
    $exclude = am($this->_excludeImage, array('image' => true, 'pos' => true));
    return $this->html->link('up', $this->getUri($query, $exclude).'#image-'.$query['image']);
  }

  function nextImage() {
    if (!isset($this->params['query']))
      return;
    $query = $this->params['query'];
    if (isset($query['nextImage'])) {
      $query['pos']++;
      $query['page'] = ceil($query['pos'] / $query['show']);
      return $this->html->link('next', '/explorer/image/'.$query['nextImage'].'/'.$this->getParams($query, $this->_excludeImage));
    }
  }
}