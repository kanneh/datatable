<?php

namespace Kanneh\Datatable;

if(!defined('DB_PREFIX')){
	define('DB_PREFIX',"");
}

class View extends Ext{
	private $editors = [];
	private ?string $viewName = null;

	private ?string $wherestr = null;

	public function __construct(Editor $editor,?string $alias = null,array $excludeColumns=[],array $renameColumns=[]){ 
		$this->addEditor($editor,$alias, $excludeColumns);
	}

	public function addEditor(Editor $editor,?string $alias = null, array $excludeColumns=[],array $renameColumns = [],array $joinCriteria = []): View{
		if($alias === null || $alias === ''){
			$alias = $editor->table;
		}
		$this->editors[] = [$alias, $editor, $excludeColumns, $renameColumns, $joinCriteria];
		return $this;
	}

	public function WHERE($wherestr,$join = 'AND', $joinWith = 'AND'): View{
		if(is_array($wherestr)){
			$wherestr = implode(" $join ",$wherestr);
		}
		
		if($this->wherestr === null){
			$this->wherestr = $wherestr;
		}else{
			$this->wherestr .= " $joinWith ( $wherestr )";
		}
		return $this;
	}

	public function getSQL($data): array {
		$sql = "SELECT ";
		$gfrom = " FROM ";
		$ffrom = " FROM ";
		$fromStr = " FROM ";
		$gsql ="";
		$params = [];
		$gparams = [];
		$options = [];
		for($i = 0; $i < count($this->editors); $i++){
			[$alias, $editor, $excludeColumns, $renameColumns, $joinCriteria] = $this->editors[$i];
			
			foreach($editor->_fields as $field){
				if(in_array($field->altname,$excludeColumns)){
					continue;
				}
				$colName = $field->altname;
				$sql .= "$alias.$field->altname";
				if(array_key_exists($colName,$renameColumns)){
					$sql .= " AS ".$renameColumns[$colName];
				}
				$sql .= ", ";
			}
			[$esql,$egsql,$egfilter,$efilter,$eoptions] = $editor->getSelectQuery($data);
			$options = array_merge($options,$eoptions);
			if($i == 0){
				$fromStr .= "($esql) AS $alias ";
				$gfrom .= "($egsql$egfilter) AS $alias ";
				$ffrom .= "($egsql$efilter) AS $alias ";
			}else{
				$fromStr .= $joinCriteria[0]." ($esql) AS $alias ON ".$joinCriteria[1]." ";
				$gfrom .= $joinCriteria[0]." ($egsql$egfilter) AS $alias ON ".$joinCriteria[1]." ";
				$ffrom .= $joinCriteria[0]." ($egsql$efilter) AS $alias ON ".$joinCriteria[1]." ";
			}

			$params = array_merge($params,$editor->getParams());
			$gparams = array_merge($gparams,$editor->searchParams);
		}
		$sql = substr($sql,0,strlen($sql)-2);
		$gsql = $sql;
		$sql .= $fromStr;
		if($this->wherestr !== null){
			$sql .= " WHERE ".$this->wherestr;
		}
		return [$sql, $gsql, $gfrom,$ffrom, $params, $gparams,$options];
	}

	public function process($data){
		[$sql, $gsql, $gfrom,$ffrom, $params, $gparams,$options] = $this->getSQL($data);
		// echo $sql;
		$mdata = $this->editors[0][1]->select($sql,$params);
		for ($i=0; $i < count($mdata); $i++) { 
			$mdata[$i]=array_merge($mdata[$i],array('DT_RowId'=>"row_".$mdata[$i][$this->editors[0][1]->_id]));
		}
		$draw=0;
		if(isset($data['draw'])){
			$draw=intval($data['draw']);
		}
		return [
			"draw"=>$draw,
			"recordsTotal"=>count($this->editors[0][1]->select($gsql.$gfrom,$gparams)),
			// "recordsTotalsql"=>$gsql.$gfrom,
			"recordsFiltered"=>count($this->editors[0][1]->select($gsql.$ffrom,$params)),
			// "recordsFilteredsql"=>$gsql.$ffrom,
			"data"=>$mdata,
			"options"=>$options,
			"error"=>$this->editors[0][1]->_out['error']
		];
	}

	function json($data, $rprint = true){
		$result = $this->process($data);
		if(!$rprint){
			return $result;
		}
		echo json_encode($result);
	}

	public function name($viewName): View{
		$this->viewName = $viewName;
		return $this;
	}

	function create($viewName = null): View{
		if($viewName !== null){
			$this->viewName = $viewName;
		}
		if($this->viewName === null){
			throw new \Exception("View name not specified. Use name() method to set view name.");
		}
		[$sql,$params] = $this->getSQL([]);
		$sql = "CREATE OR REPLACE VIEW ".$this->viewName." AS ".$sql;
		$this->editors[0][1]->query($sql,$params);
		return $this;
	}

	function drop($viewName = null){
		if($viewName !== null){
			$sql = "DROP VIEW IF EXISTS ".$viewName;
			$this->editors[0][1]->query($sql);
			return $this;
		}
		if(!$this->viewName){
			throw new \Exception("View name not specified. Use name() method to set view name.");
		}

		if($this->viewName !== null){
			$sql = "DROP VIEW IF EXISTS ".$this->viewName;
			$this->editors[0][1]->query($sql);
		}
		return $this;
	}
}


class Editor extends Ext
{
	public $data=array();
	public $joinstr="";
	public $searchstr="";
	public $db;
	public $table;
	public $_fields;
	public $_constraints;
	public $_id;
	public $_out = [];
	public $customDataProcessor;
	public $con;
	private $params = array();

	public $searchParams = array();

	public string $alias = '';

	public array $excludeColumns = [];

	function __construct($db, $tb,$id="id",$customDataProcessor=null)
	{
		$this->db=$db;
		$this->table=$tb;
		$this->_fields=[];
		$this->_constraints=[];
		$this->_id=$id;
		$this->_out=array(
			"data"=>array(),
			"error"=>"",
			"options"=>array()
		);
		$this->customDataProcessor=$customDataProcessor;
		$this->con = new \PDO($db["db"], $db["username"], $db["password"]);
		$this->con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function create()
	{
		$this->query("DROP TABLE IF EXISTS ".DB_PREFIX.$this->table);
		$sql="CREATE TABLE ".DB_PREFIX.$this->table."(";
		foreach($this->_fields as $col){
			$sql.=$col->name." ".$col->dbattr.",";
		}
		if($this->_constraints){
			foreach($this->_constraints as $cons){
				$sql.=$cons.",";
			}
		}
		$sql=substr($sql, 0,strlen($sql)-1).")";
		// print_r($sql);
		$this->query($sql);
		return $this;
	}

	function drop()
	{
		$this->query("DROP TABLE IF EXISTS ".DB_PREFIX.$this->table);
		return $this;
	}


	public function getColumns(): array{
		$dbcolumns = array_map(fn($fld)=>$fld->name,$this->_fields);
		$dbcolumns = array_filter($dbcolumns,fn($col)=>!in_array($col,$this->excludeColumns));
		if($this->alias != ''){
			$dbcolumns = array_map(fn($col)=>"$this->alias.$col",$dbcolumns);
		}
		return $dbcolumns;
	}

	public function WHERE($wherestr,$parame = [])
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
		for ($i = 0; $i < count($parame); ++$i) {
			$this->searchParams[] = $parame[$i];
		}
		return $this;
	}

	public function getSelectQuery($data): array{
		//print_r($data);
		$mycolumnsarr=[];
		$colmns=[];
		$colmnsfiler="";
		$this->params=[];
		if(isset($data['columns'])){
			for ($i=0; $i < count($data['columns']); $i++) { 
				$col=$data['columns'][$i];
				if($col['data']){
					$colmns[]=$this->getColumnName($col['data']);
				}
				$col['data']=$this->getColumnName($col['data']);
				if($col['data']){
					if(isset($col['searchable']) && $col['searchable'] == 'true' && isset($col['search']) && $col['search']['value']){
						$cstr="";
						if($data['search']['value']){
							$cstr=" (".$col['data']." LIKE ? OR ".$col['data']." LIKE ?)";
							$this->params[] = "%".$col['search']['value']."%";
							$this->params[] = "%".$data['search']['value']."%";
						}else{
							$cstr=" ".$col['data']." LIKE ?";
							$this->params[] = "%".$col['search']['value']."%";
						}
						if($colmnsfiler){
							$colmnsfiler.=" AND".$cstr;
						}else{
							$colmnsfiler=$cstr;
						}
					}elseif(isset($col['searchable']) && $col['searchable'] == 'true' && isset($data['search']) && $data['search']['value']){
						// print_r("Data search");
						$cstr=" ".($col['data'])." LIKE ?";
						$this->params[] = "%".$data['search']['value']."%";
						if($colmnsfiler){
							$colmnsfiler.=" OR".$cstr;
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
				$options[$col->altname]=$col->process($this);
			}
		}

		// print_r($colmnsfiler);

		if(!$clstr){
			$clstr="*";
		}

		$sql.=$clstr;

		$sql.=" FROM ".$this->table;
		$filter="";
		if($this->joinstr){
			$sql.=$this->joinstr;
		}
		if ($colmnsfiler) {
			$filter=" WHERE (".$colmnsfiler.")";
		}

		if(isset($data['searchBuilder'])){
			if ($filter) {
				$filter.=" AND (".$this->getCriteria($data['searchBuilder']).")";
			}else{
				$filter.=" WHERE (".$this->getCriteria($data['searchBuilder']).")";
			}
		}

		$gfilter="";
		$gsql=$sql;

		if (strlen($this->searchstr)>0) {
			if ($filter) {
				$filter.=" AND (".$this->searchstr.")";
			}else{
				$filter.=" WHERE (".$this->searchstr.")";
			}
			$gfilter=" WHERE (".$this->searchstr.")";

			for ($i=0; $i < count($this->searchParams); $i++) { 
				$this->params[] = $this->searchParams[$i];
			}
		}

		$sql.=$filter;

		
		if(isset($data['order'])){
			$orderby="";
			foreach ($data['order'] as $key) {
				if(is_int($key['column'])){
					$orderby.=$colmns[$key['column']]." ".$key['dir'];
				}else{
					$orderby.=$this->getColumnName($key['column'])." ".$key['dir'];
				}
			}
			$sql.=" order by ".$orderby;

		}

		
		if(isset($data['length'])){
			if(intval($data['length'])>0){
				$sql.=" LIMIT ".$data['start'].",".$data['length'];
			}
		}

		return [$sql,$gsql,$gfilter,$filter,$options];
	}

	public function getParams(): array{
		return $this->params;
	}

	public function process($data)
	{
		if($this->_propExists("KACTION",$data)){
			$mdata=array();
			if($data['KACTION'] != "remove"){
				// print_r($data);
				foreach($this->_fields as $col){
					if(isset($data[$col->altname])){
						$col->_getSet($col->_value,$data[$col->altname]);
						$mdata[$col->name]=$col->_getSet($col->_value,null);
						if($col->hasValidators() && !$col->validate()){
							$this->_out['error']=$col->_error;
							return $this;
						}
					}elseif($col->isUpload() && isset($_FILES[$col->altname]) && $_FILES[$col->altname]['name']){
						// print_r("process upload");
						$col->_getSet($col->_value,null);
						$mdata[$col->name]=$col->_value;
						if($col->hasValidators() && !$col->validate()){
							$this->_out['error']=$col->_error;
							return $this;
						}
					}
				}
			}
			if(empty($this->_out['error'])){
				switch ($data["KACTION"]) {
					case 'create':
						if($mdata){
							try {
								$this->insert($mdata);
								$this->_out['lastId']=$this->lastId();
							} catch (\Exception $e) {
								$this->_out['error']="Error: ".$e->getMessage();
							}
						}
						break;
					case 'update':
						if($mdata){
							$id=$data["id"];
							try {
								$this->update($mdata," WHERE ".$this->_id."=?",array($id));
								$this->_out['lastId']=$id;
							} catch (\Exception $e) {
								$this->_out['error']="Error: ".$e->getMessage();
							}
						}
						break;
					case 'remove':
						if($data){
							$id=$data['id'];
							try {
								$data=$this->select("SELECT * FROM ".$this->table." WHERE ".$this->_id."= ?",array($id))[0];
								foreach($this->_fields as $col){
									if($col->isUpload() && $data[$col->altname] != null){
										unlink($col->uploaddir.$data[$col->altname]);
									}
								}
								$this->delete(" WHERE ".$this->_id."=?",array($id));
								$this->_out['lastId']=$id;
							} catch (\Exception $e) {
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
				
				[$sql,$gsql,$gfilter,$filter,$options] = $this->getSelectQuery($data);
				// echo $sql;
				//print_r($this->db->getconnection());
				// echo $sql;
				// exit();
				$mdata=$this->select($sql,$this->params);
				for ($i=0; $i < count($mdata); $i++) { 
					$mdata[$i]=array_merge($mdata[$i],array('DT_RowId'=>"row_".$mdata[$i][$this->_id]));
				}
				$draw=0;
				if(isset($data['draw'])){
					$draw=intval($data['draw']);
				}
				
				$this->_out=array(
					"draw"=>$draw,
					"recordsTotal"=>count($this->select($gsql.$gfilter,$this->searchParams)),
					"recordsFiltered"=>count($this->select($gsql.$filter,$this->params)),
					"data"=>$mdata,
					"fields"=>$this->_fields,
					"options"=>$options,
					"error"=>$this->_out['error']
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

	public function constraints($_=null)
	{
		if($_ != null && !is_array($_)){
			$_=func_get_args();
		}
		$this->_getSet($this->_constraints,$_,true);
		$this->_out['constraints']=$this->_constraints;
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
			case '!starts':
				return $criteria['origData']." NOT LIKE '".$criteria['value1']."%'";
			case 'ends':
				return $criteria['origData']." LIKE '%".$criteria['value1']."'";
			case '!ends':
				return $criteria['origData']." NOT LIKE '%".$criteria['value1']."'";
			case 'contains':
				return $criteria['origData']." LIKE '%".$criteria['value1']."%'";
			case '!contains':
				return $criteria['origData']." NOT LIKE '%".$criteria['value1']."%'";
			case '=':
				return $criteria['origData']." = '".$criteria['value1']."'";
			case '!=':
				return $criteria['origData']." <> '".$criteria['value1']."'";
			case '<':
				return $criteria['origData']." < '".$criteria['value1']."'";
			case 'null':
				return $criteria['origData']." IS NULL";
			case '!null':
				return $criteria['origData']." IS NOT NULL";
			case 'between':
				return $criteria['origData']." ".$criteria['condition']." '".$criteria['value1']."' AND '".$criteria['value2']."'";
			case '!between':
				return $criteria['origData']." NOT BETWEEN '".$criteria['value1']."' AND '".$criteria['value2']."'";
			default:
				return $criteria['origData']." ".$criteria['condition']." '".$criteria['value1']."'";
		}
	}

	
	public function select($sql='',$params=array()){
		// print_r($params);
		if($sql == ''){
			$sql = "SELECT * FROM ".$this->table;
		}
		$mdata = $this->query($sql,$params);
		if($mdata === FALSE){
			return array();
		}else{
			return $mdata->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

	public function query($sql = '',$params = array()){
		try {
			$q = $this->con->prepare($sql);
			if (!empty($params)) {
				for ($i = 0; $i < count($params); $i++) {
					$q->bindValue($i+1,$params[$i]);
				}
			}
			$q->execute();
			return $q;
		} catch (\PDOException $e) {
			$this->_out['error'] = $e->getMessage();
			return False;
		}catch (\ValueError $e) {
			$this->_out['error'] = $e->getMessage();
			return False;
		}
	}

	public function insert($data=array())
	{
		$sql="INSERT INTO ".$this->table." SET ";
		$this->params = array();
		foreach ($data as $key => $value) {
			$sql.=$key." = ?, ";
			$this->params[] = $value;
		}
		$sql = substr($sql,0, strlen($sql) - 2);
		return $this->query($sql,$this->params);
	}

	public function update($data=array(),$filter="",$params=array())
	{
		$sql="UPDATE ".$this->table." SET ";
		$this->params = array();
		foreach ($data as $key => $value) {
			$sql.=$key." = ?, ";
			$this->params[] = $value;
		}
		$sql = substr($sql,0, strlen($sql) - 2);
		$sql.=$filter;
		for ($i = 0; $i < count($params); ++$i) {
			$this->params[] = $params[$i];
		}
		return $this->query($sql,$this->params);
	}

	public function delete($filter,$params)
	{
		$sql="DELETE FROM ".$this->table.$filter;
		return $this->query($sql,$params);
	}

	public function lastId(){
		return $this->con->lastInsertId();
	}
}

class Validator extends Ext
{
	public $type;
	private $value;
	private $oper;
	public $message;
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

	public $name;
	public $altname;

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
		return count($this->_validators) || strlen($this->_error);
	}
	public function validate()
	{
		if ($this->_error) {
			return false;
		}
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

	public $uploaddir;
	public $_resultfile;
	public function upload($setvalue=false)
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
		if($setvalue){
			$this->_value = $this->_resultfile;
		}
		unset($_FILES[$this->altname]);
		return;
	}

	protected function _getSet( &$prop, $val, $array=false )
	{
		// echo "uploading called";
		// Get
		if ( $val === null && (!isset($_FILES[$this->altname]) || !$_FILES[$this->altname]['name']) ) {
			return $prop;
		}

		// print_r($value);

		// Set
		if (isset($_FILES[$this->altname]) && $_FILES[$this->altname]['name']) {
			// print_r("expression");
			if(file_exists($this->uploaddir.$val) && !is_dir($this->uploaddir.$val)){
				unlink($this->uploaddir.$val);
			}
			$this->upload(true);
		}else{
			$prop = $val;
		}
		

		return $this;
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
		$sql = "SELECT ".$this->textcolumn.", ".$this->valuecolumn." FROM ".$this->table.$this->searchstr;
		$dbdata=$db->select($sql);
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
