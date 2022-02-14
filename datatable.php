<?php

namespace KCSL\DataTable;

class Editor extends Ext
{
	private $data=array();

	function __construct($db, $tb,$id="id")
	{
		$this->db=$db;
		$this->table=$tb;
		$this->_fields=[];
		$this->_id=$id;
		$this->_out=array(
			"data"=>array(),
			"error"=>"",
			"options"=>array()
		);
	}

	public function process($data)
	{
		if($this->_propExists("KACTION",$data)){
			$mdata=array();
			foreach($this->_fields as $col){
				if(isset($data[$col->altname])){
					$mdata[$col->name]=$data[$col->altname];
					$col->_getSet($col->_value,$data[$col->altname]);
					if($col->hasValidators() && !$col->validate()){
						$this->_out['error']=$col->_error;
						return $this;
					}
				}
			}
			switch ($data["KACTION"]) {
				case 'create':
					if($mdata){
						try {
							$this->db->insert($this->table,$mdata);
							$this->_out['lastId']=$this->db->lastId();
						} catch (Exception $e) {
							$this->_out['error']="Error: ".$e->getMessage();
						}
					}
					break;
				case 'update':
					if($mdata){
						$id=$data[$this->_id];
						try {
							$this->db->update($this->table,$mdata," WHERE ".$this->_id."=".$id);
						} catch (Exception $e) {
							$this->_out['error']="Error: ".$e->getMessage();
						}
					}
					break;
				case 'remove':
					if($mdata){
						$id=$data[$this->_id];
						try {
							$this->db->delete($this->table," WHERE ".$this->_id."=".$id);
						} catch (Exception $e) {
							$this->_out['error']="Error: ".$e->getMessage();
						}
					}
					break;
				default:
					$this->_out['error']="Error: Unknown action.";
			}
		}else{
			//print_r($data);
			$colmns=array();
			$colmnsfiler="";
			for ($i=0; $i < count($data['columns']); $i++) { 
				$col=$data['columns'][$i];
				if($col['data']){
					$colmns[]=$col['data'];
				}
				if($col['searchable'] == 'true' && $col['search']['value']){
					$cstr="";
					if($data['search']['value']){
						$cstr=" (".$col['data']." LIKE '%".$col['search']['value']."%' OR ".$col['data']." LIKE '%".$data['search']['value']."%')";
					}else{
						$cstr=" ".$col['data']." LIKE '%".$col['search']['value']."%'";
					}
					if($colmnsfiler){
						$colmnsfiler.=" AND".$cstr;
					}else{
						$colmnsfiler=$cstr;
					}
				}elseif($col['searchable'] == 'true' && $data['search']['value']){
					$cstr="  ".$col['data']." LIKE '%".$data['search']['value']."%'";
					if($colmnsfiler){
						$colmnsfiler.=" AND".$cstr;
					}else{
						$colmnsfiler=$cstr;
					}
				}
			}
			$colmns=array_merge($colmns,array($this->_id));




			$sql="SELECT ";

			/*if($colmns){
				$sql.=implode(",", $colmns);
			}else{
				$sql.="*";
			}*/

			$clstr="";
			foreach($this->_fields as $col){
				if(!$clstr){
					$clstr=$col->name." AS ".$col->altname;
				}else{
					$clstr.=", ".$col->name." AS ".$col->altname;
				}
			}

			if(!$clstr){
				$clstr="*";
			}

			$sql.=$clstr;

			$sql.=" FROM ".DB_PREFIX.$this->table;
			$filter="";
			if ($colmnsfiler) {
				$sql.=" WHERE".$colmnsfiler;
				$filter=" WHERE".$colmnsfiler;
			}

			if(isset($data['searchBuilder'])){
				if ($filter) {
					$sql.=" AND ".$this->getCriteria($data['searchBuilder']);
					$filter.=" AND ".$this->getCriteria($data['searchBuilder']);
				}else{
					$sql.=" WHERE ".$this->getCriteria($data['searchBuilder']);
					$filter.=" WHERE ".$this->getCriteria($data['searchBuilder']);
				}
			}

			$orderby="";
			foreach ($data['order'] as $key) {
				$orderby.=$colmns[$key['column']]." ".$key['dir'];
			}

			$sql.=" order by ".$orderby." LIMIT ".$data['start'].",".$data['length'];

			//echo $sql;

			$mdata=$this->db->query($sql)->rows;
			for ($i=0; $i < count($mdata); $i++) { 
				$mdata[$i]=array_merge($mdata[$i],array('DT_RowId'=>"row_".$mdata[$i][$this->_id]));
			}

			$this->_out=array(
				"draw"=>intval($data['draw']),
				"recordsTotal"=>$this->db->count($this->table),
				"recordsFiltered"=>$this->db->count($this->table,$filter),
				"data"=>$mdata,
				"fields"=>$this->_fields,
				"sql"=>$sql
			);
		}
		return $this;
	}

	public function fields($_=null)
	{
		if($_ != null && !is_array($_)){
			$_=func_get_args();
		}
		$this->_getSet($this->_fields,$_,true);
		$this->_out['fields']=$this->_fields;
		return $this;
	}



	public function json()
	{
		echo json_encode($this->_out);
	}

	public function getCriteria($criteria)
	{
		$ct=$criteria["criteria"];
		$ct0=$ct[0];
		$stq="(";
		$stq.=$this->getCriteriaValue($ct0);
		for($i=1;$i<count($ct);$i++){
			$stq.=" ".$criteria['logic']." ".$this->getCriteriaValue($ct[$i]);
		}
		return $stq.")";
	}
	public function getCriteriaValue($criteria)
	{
		if(isset($criteria['criteria'])){
			return $this->getCriteria($criteria);
		}
		switch ($criteria['condition']) {
			case 'starts':
				return $criteria['origData']." LIKE '".$criteria['value1']."%'";
				break;
			case '!starts':
				return $criteria['origData']." NOT LIKE '".$criteria['value1']."%'";
				break;
			case 'ends':
				return $criteria['origData']." LIKE '%".$criteria['value1']."'";
				break;
			case '!ends':
				return $criteria['origData']." NOT LIKE '%".$criteria['value1']."'";
				break;
			case 'contains':
				return $criteria['origData']." LIKE '%".$criteria['value1']."%'";
				break;
			case '!contains':
				return $criteria['origData']." NOT LIKE '%".$criteria['value1']."%'";
				break;
			case '=':
				return $criteria['origData']." = '".$criteria['value1']."'";
				break;
			case '!=':
				return $criteria['origData']." <> '".$criteria['value1']."'";
				break;
			case 'null':
				return $criteria['origData']." IS NULL";
				break;
			case '!null':
				return $criteria['origData']." IS NOT NULL";
				break;
		}
	}
}

class Validator extends Ext
{
	function __construct($type,$value,$oper="=",$errmsg="invalid")
	{
		$this->type=$type;
		$this->value=$value;
		$this->oper=$oper;
		$this->message=$errmsg;
	}
	public function test($value)
	{
		switch($this->type){
			case 'length':
				return $this->assert(strlen($value));
			case 'nonull':
				return $this->assert(strlen($value));
		}
	}
	public function assert($value)
	{
		switch($this->oper){
			case "<=":
				return $value <= $this->value;
			case ">":
				return $value > $this->value;
		}
	}
}

class Field extends Ext
{
	public $_validators=array();
	public $_error="";
	public $_value="";
	function __construct($name,$altname=null,$req=false,$reqmes="Not all required fields have been filled")
	{
		$this->name=$name;
		$this->altname=$altname?$altname:$name;
		if ($req) {
			$this->_validators[]=Validator::inst("nonull",0,">",$reqmes);
		}
	}
	public function validators($_=null)
	{
		if($_ != null && !is_array($_)){
			$_=func_get_args();
		}
		$this->_getSet($this->_validators,$_,true);
		return $this;
	}
	public function hasValidators()
	{
		return count($this->_validators);
	}
	public function validate()
	{
		foreach($this->_validators as $vald){
			if(!$vald->test($this->_value)){
				$this->_error=$vald->message;
				return false;
			}
		}
		return true;
	}
}


class Ext {
	public static function instantiate ()
	{
		$rc = new \ReflectionClass( get_called_class() );
		$args = func_get_args();

		return count( $args ) === 0 ?
			$rc->newInstance() :
			$rc->newInstanceArgs( $args );
	}

	public static function inst ()
	{
		$rc = new \ReflectionClass( get_called_class() );
		$args = func_get_args();

		return count( $args ) === 0 ?
			$rc->newInstance() :
			$rc->newInstanceArgs( $args );
	}

	protected function _getSet( &$prop, $val, $array=false )
	{
		// Get
		if ( $val === null ) {
			return $prop;
		}

		// Set
		if ( $array ) {
			// Property is an array, merge or add to array
			is_array( $val ) ?
				$prop = array_merge( $prop, $val ) :
				$prop[] = $val;
		}
		else {
			// Property is just a value
			$prop = $val;
		}

		return $this;
	}

	
	protected function _propExists ( $name, $data )
	{
		if ( strpos($name, '.') === false ) {
			return isset( $data[ $name ] );
		}

		$names = explode( '.', $name );
		$inner = $data;

		for ( $i=0 ; $i<count($names)-1 ; $i++ ) {
			if ( ! isset( $inner[ $names[$i] ] ) ) {
				return false;
			}

			$inner = $inner[ $names[$i] ];
		}

		if ( isset( $names[count($names)-1] ) ) {
			$idx = $names[count($names)-1];

			return isset( $inner[ $idx ] );
		}

		return false;
	}

	
	protected function _readProp ( $name, $data )
	{
		if ( strpos($name, '.') === false ) {
			return isset( $data[ $name ] ) ?
				$data[ $name ] :
				null;
		}

		$names = explode( '.', $name );
		$inner = $data;

		for ( $i=0 ; $i<count($names)-1 ; $i++ ) {
			if ( ! isset( $inner[ $names[$i] ] ) ) {
				return null;
			}

			$inner = $inner[ $names[$i] ];
		}

		if ( isset( $names[count($names)-1] ) ) {
			$idx = $names[count($names)-1];

			return isset( $inner[ $idx ] ) ?
				$inner[ $idx ] :
				null;
		}

		return null;
	}

	
	protected function _writeProp( &$out, $name, $value )
	{
		if ( strpos($name, '.') === false ) {
			$out[ $name ] = $value;
			return;
		}

		$names = explode( '.', $name );
		$inner = &$out;
		for ( $i=0 ; $i<count($names)-1 ; $i++ ) {
			$loopName = $names[$i];

			if ( ! isset( $inner[ $loopName ] ) ) {
				$inner[ $loopName ] = array();
			}
			else if ( ! is_array( $inner[ $loopName ] ) ) {
				throw new \Exception(
					'A property with the name `'.$name.'` already exists. This '.
					'can occur if you have properties which share a prefix - '.
					'for example `name` and `name.first`.'
				);
			}

			$inner = &$inner[ $loopName ];
		}

		if ( isset( $inner[ $names[count($names)-1] ] ) ) {
			throw new \Exception(
				'Duplicate field detected - a field with the name `'.$name.'` '.
				'already exists.'
			);
		}

		$inner[ $names[count($names)-1] ] = $value;
	}
}