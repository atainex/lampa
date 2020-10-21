<?php

namespace Lampa;

class ORM
{
	protected $_table_name = '';
	protected $_primary_key	= 'id';

	protected $_loaded = false;
	protected $_describe = array();
	protected $_sql = '';
	protected $_sqlStates = array(
		'select' => array(
			'state' => false,
			'arguments' => array(),
		),
		'join' => array(
			'state' => false,
			'arguments' => array(),
		),
		'where' => array(
			'state' => false,
			'arguments' => array(),
		),
		'limit' => array(
			'state' => false,
			'arguments' => array(),
		),
		'order_by' => array(
			'state' => false,
			'arguments' => array(),
		),
	);
	public $_more = array();
	
	function __construct() {
		$query = 'DESCRIBE '.$this->_table_name;
		$this->_describe = DB::factory()->run($query)->fetchAll();
	}
	
	public static function factory($name)
	{
		$mName = 'App\\Models\\' . $name;
		return new $mName();
	}
	
	public function _pre_save() {
		
	}
	
	public function select($name, $as = null) {
		$this->_sqlStates['select']['state'] = true;
		$this->_sqlStates['select']['arguments'][] = array(
			'name' => $name,
			'as' => $as,
		);
		return $this;
	}
	
	public function _where($name, $op, $value, $char = 'AND') {
		$this->_sqlStates['where']['state'] = true;
		$this->_sqlStates['where']['arguments'][] = array(
			'char' => $char,
			'name' => $name,
			'op' => $op,
			'value' => $value
		);
		return $this;
	}

	public function where($name, $op, $value) {
		$this->_where($name, $op, $value);
		return $this;
	}

	public function and_where($name, $op, $value) {
		$this->_where($name, $op, $value, 'AND');
		return $this;
	}

	public function or_where($name, $op, $value) {
		$this->_where($name, $op, $value, 'OR');
		return $this;
	}

	public function join($name, $way, $on) {
		$this->_sqlStates['join']['state'] = true;
		if (is_array($name)) {
			$this->_sqlStates['join']['arguments'][] = array(
				'name' => $name[0],
				'as' => $name[1],
				'way' => $way,
				'on' => $on,
			);
		} else {			
			$this->_sqlStates['join']['arguments'][] = array(
				'name' => $name,
				'as' => $name,
				'way' => $way,
				'on' => $on,
			);
		}
		return $this;
	}

	public function order_by($name, $way) {
		$this->_sqlStates['order_by']['state'] = true;		
		$this->_sqlStates['order_by']['arguments'][] = array(
			'name' => $name,
			'way' => $way,
		);
		return $this;
	}
	
	public function execute() {
		if ($this->_sqlStates['join']['state']) {
			foreach ($this->_sqlStates['join']['arguments'] as $join) {				
				$this->_sql .= ' '. $join['way'] . ' JOIN ' . $join['name'] . ' as ' . $join['as'] . ' ON ('. $join['on'] . ') ';
			}
		}
		if ($this->_sqlStates['where']['state']) {
			$this->_sql .= ' WHERE (';
			$first = true;
			foreach ($this->_sqlStates['where']['arguments'] as $where) {
				if ($first) {					
					$this->_sql .= ' ' . $where['name'] . ' ' . $where['op'] . ' \'' . $where['value'] . '\' '; 
					$first = false;
				} else {
					$this->_sql .= $where['char'] . ' ' . $where['name'] . ' ' . $where['op'] . ' \'' . $where['value'] . '\' '; 
				}
			}
			$this->_sql .= ' ) ';
		}
		if ($this->_sqlStates['order_by']['state']) {
			$this->_sql .= ' ORDER BY ';
			$d = [];
			foreach ($this->_sqlStates['order_by']['arguments'] as $order_by) {
				$d[] = $order_by['name'] . ' ' . $order_by['way'];
			}
			$this->_sql .= implode(', ', $d);
		}
		if ($this->_sqlStates['limit']['state']) {
			$this->_sql .= ' LIMIT ' . $this->_sqlStates['limit']['arguments'] . ' ';
		}
		return $this;
	}
	
	public function find() {
		$_data = array();
		foreach ($this->_describe as $d) {
			$_data[$d['Field']] = null;
		}
		$this->_sql .= $this->buildQuery($this->_table_name, 'select', $_data);
		$this->_sqlStates['limit']['state'] = true;
		$this->_sqlStates['limit']['arguments'] = '1';
		$this->execute();
		$result = DB::factory()->run($this->_sql)->fetch();
		foreach ($result as $k => $v) {
			$this->$k = $v;
			$this->_loaded = true;
		}
		return $this;
	}
	
	public function find_all() {
		$_data = array();
		foreach ($this->_describe as $d) {
			$_data[$d['Field']] = null;
		}
		$this->_sql .= $this->buildQuery($this->_table_name, 'select', $_data);
		$this->execute();
		$result = DB::factory()->run($this->_sql)->fetchAll();
		foreach ($result as $obj) {
			$className = get_class($this);
			$this->_more[$obj['id']] = new $className();
			foreach ($obj as $k => $v) {
				$this->_more[$obj['id']]->$k = $v;
			}
			$this->_more[$obj['id']]->_loaded = true;
		}
		return $this->_more;
	}
	
	public function getForeach() {
		return $this->_more;
	}
	
	public function set($name, $value) {
		$this->{$name} = $value;
	}
	
	public function loaded() {
		if ($this->_loaded) {
			return true;
		}
		return false;
	}
	
	public function save() {
		$this->_pre_save();
		$query = '';
		$_data = array();
		if ($this->loaded()) {
			$this->update();
		} else {			
			foreach ($this->_describe as $d) {
				if (isset($this->{$d['Field']})) {
					$_data[$d['Field']] = $this->{$d['Field']};
				}
			}
			$query .= $this->buildQuery($this->_table_name, 'insert', $_data);
			$result = DB::factory()->insertAndGetLastId($query);
			if (empty($result)) {
				throw new Exception('Ошибка добавления записи в БД');
			}
			$this->id = $result;
			$this->_loaded = true;
		}
	}
	
	public function update() {
		$this->_pre_save();
		
		if (!$this->getTargetState()) {
			throw new Exception('Невозможно обновить запись: не определён первичный ключ');
		}
		$_data = array();
		foreach ($this->_describe as $d) {
			if (isset($this->{$d['Field']})) {
				$_data[$d['Field']] = $this->{$d['Field']};
			}
		}
		$query .= $this->buildQuery($this->_table_name, 'update', $_data);
		if (is_array($this->_primary_key)) {
			$pre = '';
			$query .= ' WHERE ';
			foreach ($this->_primary_key as $k => $v) {
				$query .= $pre.'`'.$this->_table_name.'`.`'.$k.'` = '.DB::factory()->pdo->quote($this->{$k}).' ';
				$pre = 'AND ';
			}
			$result = DB::factory()->run($query)->execute();
		} else {
			$query .= ' WHERE `'.$this->_table_name.'`.`'.$this->_primary_key.'` = ' . DB::factory()->pdo->quote($this->{$this->_primary_key});
			$result = DB::factory()->run($query)->execute();	
		}
		return $this;
	}
	
	public function delete() {
		if (!$this->getTargetState()) {
			throw new Exception('Невозможно удалить запись: не определён первичный ключ');
		}
		if (is_array($this->_primary_key)) {
			$pre = '';
			$query = ' DELETE FROM `'.$this->_table_name.'` WHERE ';
			foreach ($this->_primary_key as $k => $v) {
				$query .= $pre.'`'.$this->_table_name.'`.`'.$k.'` = '.DB::factory()->pdo->quote($this->{$k}).' ';
				$pre = 'AND ';
			}
			$result = DB::factory()->run($query)->execute();
			return $result;		
		} else {
			$query = ' DELETE FROM `'.$this->_table_name.'` WHERE `'.$this->_table_name.'`.`'.$this->_primary_key.'` = ' . DB::factory()->pdo->quote($this->{$this->_primary_key});
			$result = DB::factory()->run($query)->execute();
			return $result;		
		}
		return false;
	}
	
	public function getTargetState() {
		if (is_array($this->_primary_key)) {
			$empty = false;
			foreach ($this->_primary_key as $k => $v) {
				if (empty($this->{$k})) {
					$empty = true;
				}
			}
			if (!$empty) {
				return true;
			}
		} else {
			if (!empty($this->{$this->_primary_key})) {
				return true;
			}
		}
		return false;
	}
	
	public function buildQuery($table, $type, $array, $other = array()) {
		$query = '';
		$keys = array();
		$values = array();
		$args = array();
		foreach ($array as $k => $v) {
			$keys[] = '`'.$table.'`.`'.$k.'`';
			$values[] = DB::factory()->pdo->quote($v);
			$args[] = '`'.$table.'`.`'.$k.'` = '.DB::factory()->pdo->quote($v);
		}
		switch ($type) {
			case 'insert':
				$query .= 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
				break;
			case 'update':
				$query .= 'UPDATE '.$table.' SET '.implode(', ', $args).'';
				break;
			case 'select':
				if ($this->_sqlStates['select']['state']) {
					foreach ($this->_sqlStates['select']['arguments'] as $v) {
						$keys[] = (!empty($v['as'])) ? $v['name'] . ' as ' . $v['as'] : $v['name'];
					}
				}
				$query .= 'SELECT '.implode(', ', $keys).' FROM '.$table;
				break;
			case 'delete':
				$query .= 'DELETE FROM '.$table.' WHERE id = ' . DB::factory()->pdo->quote($array['id']);
				break;
			default:
				return false;
		}
		return $query;
	}
}