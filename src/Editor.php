<?php

namespace Kanneh\Datatable;

class Editor extends Ext
{
	private $data=array();
	private $joinstr="";
	private $searchstr="";

	function __construct($db, $tb,$id="id",$customDataProcessor=null)
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
		$this->customDataProcessor=$customDataProcessor;
	}

	public function create()
	{
		$sql="CREATE TABLE ".DB_PREFIX.$this->table."(";
		foreach($this->_fields as $col){
			$sql.=$col->name." ".$col->dbattr.",";
		}
		$sql=substr($sql, 0,strlen($sql)-1).")";
		$this->db->query($sql);
		return $this;
	}

	public function WHERE($wherestr)
	{
		if(is_array($wherestr)){
			foreach($wherestr as $whr){
				$this->WHERE($whr);
			}
			return $this;
		}
		if(strlen($this->searchstr)>0){
			$this->searchstr.=" AND ".$wherestr;
		}else{
			$this->searchstr=$wherestr;
		}
		return $this;
	}

	public function process($data)
	{
		if($this->_propExists("KACTION",$data)){
			$mdata=array();
			if($data['KACTION'] != "remove"){
				//print_r($data['KACTION']);
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
						$id=$data["id"];
						try {
							$this->db->update($this->table,$mdata," WHERE ".$this->_id."=".$id);
						} catch (Exception $e) {
							$this->_out['error']="Error: ".$e->getMessage();
						}
					}
					break;
				case 'remove':
					if($data){
						$id=$data['id'];
						try {
							$this->db->delete($this->table," WHERE ".$this->_id."=".$id);
						} catch (Exception $e) {
							$this->_out['error']="Error: ".$e->getMessage();
						}
					}
					break;
				case 'upload':
					//print_r($data);
					foreach($this->_fields as $col){
						if($col->isUpload() && $col->altname == $data['feild']){
							//echo "found";
							$col->upload();
							if($col->_status){
								$this->_out['uploadedfilepath']=$col->_resultfile;
							}else{
								$this->_out['error']=$col->_error;
							}
						}
					}
					break;
				case 'options':
					$options=array();
					//print_r($this->_fields);
					foreach($this->_fields as $col){
						if($col->hasoption){
							$options[$col->altname]=$col->process($this->db);
						}
					}
					$this->_out['options']=$options;
					break;
				default:
					$this->_out['error']="Error: Unknown action.";
			}
			return $this;
		}else{
			if($this->customDataProcessor){
				require_once $this->customDataProcessor;
				$this->_out=array(
					"draw"=>intval($data['draw']),
					"recordsTotal"=>$recordsTotal,
					"recordsFiltered"=>$recordsFiltered,
					"data"=>$reqData
				);
			}else{
				//print_r($data);
				$mycolumnsarr=array();
				$colmns=array();
				$colmnsfiler="";
				if(isset($data['columns'])){
					for ($i=0; $i < count($data['columns']); $i++) { 
						$col=$data['columns'][$i];
						if($col['data']){
							$colmns[]=$this->getColumnName($col['data']);
						}
						$col['data']=$this->getColumnName($col['data']);
						if($col['data']){
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
				$options=array();
				//print_r($this->_fields);
				foreach($this->_fields as $col){
					if(!$clstr){
						$clstr=$col->name." AS ".$col->altname;
					}else{
						$clstr.=", ".$col->name." AS ".$col->altname;
					}
					if($col->hasoption){
						$options[$col->altname]=$col->process($this->db);
					}
				}

				//print_r($colmnsfiler);

				if(!$clstr){
					$clstr="*";
				}

				$sql.=$clstr;

				$sql.=" FROM ".DB_PREFIX.$this->table;
				$filter="";
				if($this->joinstr){
					$sql.=$this->joinstr;
				}
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

				$gfilter="";

				if (strlen($this->searchstr)>0) {
					if ($filter) {
						$sql.=" AND ".$this->searchstr;
						$filter.=" AND ".$this->searchstr;
					}else{
						$sql.=" WHERE ".$this->searchstr;
						$filter.=" WHERE ".$this->searchstr;
					}
					$gfilter=" WHERE ".$this->searchstr;
				}

				
				if(isset($data['order'])){
					$orderby="";
					foreach ($data['order'] as $key) {
						$orderby.=$colmns[$key['column']]." ".$key['dir'];
					}
					$sql.=" order by ".$orderby;

				}

				
				if(isset($data['length'])){
					if(intval($data['length'])>0){
						$sql.=" LIMIT ".$data['start'].",".$data['length'];
					}
				}

				//echo $sql;
				//print_r($this->db->getconnection());

				$mdata=$this->db->query($sql)->rows;
				for ($i=0; $i < count($mdata); $i++) { 
					$mdata[$i]=array_merge($mdata[$i],array('DT_RowId'=>"row_".$mdata[$i][$this->_id]));
				}
				$draw=0;
				if(isset($data['draw'])){
					$draw=intval($data['draw']);
				}

				$this->_out=array(
					"draw"=>$draw,
					"recordsTotal"=>$this->db->count($this->table,$gfilter),
					"recordsFiltered"=>count($mdata),
					"data"=>$mdata,
					"fields"=>$this->_fields,
					"sql"=>$sql,
					"options"=>$options
				);
			}
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

	public function join($jointype,$jointable,$leftoprand,$operator,$rightoperand)
	{
		if($this->joinstr){
			$this->joinstr.=" ".$jointype." join ".$jointable." on ".$leftoprand." ".$operator." ".$rightoperand;
			return $this;
		}
		$this->joinstr=" ".$jointype." join ".$jointable." on ".$leftoprand." ".$operator." ".$rightoperand;
		return $this;
	}

	public function json($rprint=true)
	{
		if(!$rprint){
			return $this->_out;
		}
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
	public function getColumnName($caltname)
	{
		foreach($this->_fields as $col){
			if($col->altname == $caltname){
				return $col->name;
			}
		}
		return 0;
	}
	public function getCriteriaValue($criteria)
	{
		if(isset($criteria['criteria'])){
			return $this->getCriteria($criteria);
		}
		$criteria['origData']=$this->getColumnName($criteria['origData']);
		//print_r($criteria);
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
			case '<':
				return $criteria['origData']." < '".$criteria['value1']."'";
				break;
			case 'null':
				return $criteria['origData']." IS NULL";
				break;
			case '!null':
				return $criteria['origData']." IS NOT NULL";
				break;
			case 'between':
				return $criteria['origData']." ".$criteria['condition']." '".$criteria['value1']."' AND '".$criteria['value2']."'";
				break;
			case '!between':
				return $criteria['origData']." NOT BETWEEN '".$criteria['value1']."' AND '".$criteria['value2']."'";
				break;
			default:
				return $criteria['origData']." ".$criteria['condition']." '".$criteria['value1']."'";
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
	private $option=null;
	public $hasoption=false;
	public $dbattr="VARCHAR(50)";

	public function isUpload()
	{
		return false;
	}
	function __construct($name,$altname=null,$req=false,$reqmes="Not all required fields have been filled")
	{
		$this->name=$name;
		$this->altname=$altname?$altname:$name;
		if ($req) {
			$this->_validators[]=Validator::inst("nonull",0,">",$reqmes);
		}
	}
	public function dbAttr($attrb)
	{
		$this->dbattr=$attrb;
		return $this;
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
	public function option($opt)
	{
		$this->option=$opt;
		$this->hasoption=true;
		return $this;
	}
	public function process($db)
	{
		return $this->option->process($db);
	}
}

class Upload extends Field
{
	public $_error="";
	public $_status=1;
	public $allowedFileSize=500;
	public $allowedFileTypes=array();
	public function upload()
	{
		if (!isset($_FILES[$this->altname])) {
			$this->_error="Invalid File";
			$this->_status=0;
			return;
		}
		$filetp=$_FILES[$this->altname];
		$tgfile=$this->uploaddir.str_replace(explode(".",$filetp["name"])[0],uniqid('',true).(explode(".",$filetp["name"])[0]),$filetp["name"]);
		if (!is_dir($this->uploaddir)) {
			mkdir($this->uploaddir,0777,true);
		}
		if ($filetp['size'] > $this->allowedFileSize) {
			$this->_error="File size: ".$filetp['size']." exceeds maximum allowed size:".$this->allowedFileSize." File not uploaded";
			$this->_status=0;
			return;
		}
		if (!in_array($filetp['type'], $this->allowedFileTypes)) {
			$this->_error="Unsupported file type: ".$filetp['type'].". File not uploaded";
			$this->_status=0;
			return;
		}
		if (!move_uploaded_file($filetp['tmp_name'], $tgfile)) {
			$this->_error="Unknown error occured while uploading file. File not uploaded";
			$this->_status=0;
			return;
		}
		$this->_resultfile=str_replace($this->uploaddir,"",$tgfile);
		return;
	}
	public function isUpload()
	{
		return true;
	}
	public function dir($updir="/")
	{
		$this->uploaddir=$updir;
		return $this;
	}
	public function fileType($filt=array())
	{
		$this->allowedFileTypes=$filt;
		return $this;
	}
	public function size($sz)
	{
		$this->allowedFileSize=$sz;
		return $this;
	}
}

/**
 * Options
 */
class Options extends Ext
{
	private $table,$valuecolumn,$textcolumn;
	private $searchstr="";

	function __construct($table,$vcol=null,$tcol=null)
	{
		$this->table=$table;
		$this->valuecolumn=$vcol;
		$this->textcolumn=$tcol;
	}
	public function process($db)
	{
		if(is_array($this->table)){
			return $this->table;
		}
		if (strlen($this->searchstr)>0) {
			$this->searchstr=" WHERE ".$this->searchstr;
		}
		$dbdata=$db->select($this->table,array($this->textcolumn,$this->valuecolumn),$this->searchstr)->rows;
		$rdata=array();
		foreach ($dbdata as $dt) {
			$rdata[]=array(
				"text"=>$dt[$this->textcolumn],
				"value"=>$dt[$this->valuecolumn]
			);
		}
		return $rdata;
	}
	public function WHERE($wherestr)
	{
		if(is_array($wherestr)){
			foreach($wherestr as $whr){
				$this->WHERE($whr);
			}
			return $this;
		}
		if(strlen($this->searchstr)>0){
			$this->searchstr.=" AND ".$wherestr;
		}else{
			$this->searchstr=$wherestr;
		}
		return $this;
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