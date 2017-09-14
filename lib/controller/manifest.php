<?php
namespace lib\controller;
class manifest{
	public static $manifest_file_return = array();
	public $repository_name;
	public $manifest = array();
	public function __construct($_repository_name = null){
		$this->repository_name = $_repository_name === null ? \lib\router::get_repository_name() : $_repository_name;
		$include_path = $this->manifest_get_file_list();
		foreach ($include_path as $key => $manifest_file) {
			$this->manifest_include_file($manifest_file);
		}
	}

	/**
	 * require an execommand manifest file
	 * @param  string $_file_name manifest file name
	 */
	public function manifest_include_file($_file_name){
		if(!array_key_exists($_file_name, manifest::$manifest_file_return)){
			$manifest = object();
			$req = require_once $_file_name;
			$manifest = (array) $manifest;
			$manifest = is_array($req) ? $req : $manifest;
			manifest::$manifest_file_return[$_file_name] = $manifest;
		}else{
			$manifest = manifest::$manifest_file_return[$_file_name];
		}
		if(is_array($manifest)){
			$this->manifest_analyzer($manifest);
		}
	}

	/**
	 * array list of manifest files
	 * @return array manifest file list
	 */
	public function manifest_get_file_list(){
		$include_path = array();
		$file_lists = explode(PATH_SEPARATOR, get_include_path());
		array_unshift($file_lists, root);
		krsort($file_lists);
		foreach ($file_lists as $key => $value) {
			$manifest_file = join(DIRECTORY_SEPARATOR, [rtrim($value, '/'), $this->repository_name, 'manifest.php']);
			if(file_exists($manifest_file)){
				array_push($include_path, $manifest_file);
			}
		}
		return $include_path;
	}

	/**
	 * check manifest array keys in manifest_key_* if exists for save if self::$manifest
	 * @param  array $_manifest manifest array from manifest.php
	 */
	public function manifest_analyzer($_manifest){
		foreach ($_manifest as $key => $value) {
			$class_name = '\lib\controller\manifest\\'. $key;
			if(class_exists($class_name)){
				$manifest_sub_value = array_key_exists($key, $this->manifest) ? $this->manifest[$key] : null;
				$class_key = new $class_name($value, $manifest_sub_value);
				$this->manifest[$key] = $class_key->get();
			}else{
				call_user_func_array([$this, 'manifest_method_default'], [$key, $value]);
			}
		}
	}

	/**
	 * if manifest keys not defined run this method and check for save in $this->manifest
	 * @param  string $_method manifest key name
	 * @param  array $_values manifest value of key
	 */
	public function manifest_method_default($_method, $_values){
		foreach ($_values as $key => $value) {
			if(!array_key_exists($_method, $this->manifest)){
				$this->manifest[$_method] = array();
			}
			$this->manifest_value_add($_method, $key, $value);
		}
	}

	/**
	 * manifest check for add a key in $this->manifest array
	 * @param  string $_method manifest key name
	 * @param  string $_key    manifest subkey name
	 * @param  array $_value  manifest subkey array
	 */
	public function manifest_value_add($_method, $_key, $_value){
		if(array_key_exists($_key, $this->manifest[$_method])){
			$mod = array_key_exists('_mod', $_value) ? $_value['_mod'] : 'update';
		}else{
			$mod = 'new';
		}
		switch ($mod) {
			case 'new':
			$this->manifest[$_method][$_key] = $_value;
			break;
			case 'delete':
			unset($this->manifest[$_method][$_key]);
			break;
			default:
			if(!array_key_exists($_key, $this->manifest[$_method])){
				$this->manifest_value_add($_method, $_key, $_value);
			}else{
				foreach ($_value as $key => $value) {
					$this->manifest[$_method][$_key][$key] = $value;
				}
			}
			break;
		}
		unset($this->manifest[$_method][$_key]['_mod']);
	}

	/**
	 * return manifest array list
	 * @return array manifest array list
	 */
	public function get(){
		return $this->manifest;
	}
}
?>