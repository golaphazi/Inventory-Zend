<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	use Zend\Session\Container as SessionContainer;
	use Zend\Db\Sql\Sql;
	
	class CategoryTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function fetchAll() {
			$resultSet = $this->tableGateway->select();
			return $resultSet;
		}
		
		public function categoryTableView($paginated=false,Select $select = null) {
			
			    
			$select = "		SELECT 
								  	C.CATEGORY_ID, 
								  	rpad(' ',COUNT(C.CATEGORY_NAME)*6,'-') AS CDOT,
									C.CATEGORY_NAME AS CATEGORY_NAME,
								  	C.LFT,
								  	C.ORDER_BY,
								  	COUNT(C.CATEGORY_NAME) AS NODE_DEPTH,
									C.ACTIVE_INACTIVE AS ACTIVE_INACTIVE,
									C.P_CODE
						  FROM 
								  	ls_category C, 
								  	ls_category P
						  WHERE 
								  	C.LFT BETWEEN P.LFT AND P.RGT
						  GROUP BY 
								  	C.CATEGORY_ID, 
								  	C.CATEGORY_NAME, 
								  	C.LFT ,
								 	C.ORDER_BY,
									C.ACTIVE_INACTIVE
						  ORDER BY 
								  	C.LFT
						";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			return $resultSet;
			
			/*if($paginated) {
								
				$sql = new Sql($adapter);
				$select = $sql->select()	
                ->from(array('C' => 'ls_category'),array("C.CATEGORY_ID","rpad(' ',COUNT(C.CATEGORY_NAME)*6,'-') AS CDOT", "C.CATEGORY_NAME AS CATEGORY_NAME", "C.LFT","C.ORDER_BY", "COUNT(C.CATEGORY_NAME) AS NODE_DEPTH", "C.ACTIVE_INACTIVE AS ACTIVE_INACTIVE", "C.P_CODE"))
                ->join(array('P' => 'ls_category'), 'C.CATEGORY_ID = P.CATEGORY_ID')
               ->where('C.LFT BETWEEN P.LFT AND P.RGT')
				->group('C.CATEGORY_ID','C.CATEGORY_NAME','C.LFT','C.ORDER_BY','C.ACTIVE_INACTIVE')
               ->order('C.LFT');
			   
					
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Category());
					// create a new pagination adapter object
					$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
					$paginator 			= new Paginator($paginatorAdapter);
					return $paginator;
				
				// create a new result set based on the Investor Details entity
				
				
			}*/
		}
		
		public function categoryNodeDepth($id) {
			$select = "		
							SELECT 
								  	C.CATEGORY_ID, 
								  	rpad(' ',COUNT(C.CATEGORY_NAME)*6,'-') AS CDOT,
									C.CATEGORY_NAME AS CATEGORY_NAME,
								  	C.LFT,
								  	C.ORDER_BY,
								  	COUNT(C.CATEGORY_NAME) AS NODE_DEPTH,
									C.ACTIVE_INACTIVE AS ACTIVE_INACTIVE,
									C.COA_CODE
						  FROM 
								  	ls_category C, 
								  	ls_category P
						  WHERE 
								  	C.LFT BETWEEN P.LFT AND P.RGT
									AND   C.CATEGORY_ID = ".$id."
						  GROUP BY 
								  	C.CATEGORY_ID, 
								  	C.CATEGORY_NAME, 
								  	C.LFT ,
								 	C.ORDER_BY,
									C.ACTIVE_INACTIVE,
									C.COA_CODE
						  ORDER BY 
								  	C.LFT
						";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			return $resultSet;
		}
		
		public function getCategoryOrder($parentControllerId) {
			$select = "		SELECT 
								(COALESCE(MAX(C.ORDER_BY),0) + 1) AS NODE_DEPTH
							FROM 
								ls_category C, 
								ls_category P,
								(
								  SELECT  
									C.CATEGORY_ID,
									COUNT(C.CATEGORY_NAME) AS NODE_DEPTH
								  FROM 
									ls_category C, 
									ls_category P
								  WHERE 
									C.LFT BETWEEN P.LFT AND P.RGT
								  GROUP BY 
									C.CATEGORY_ID,
									C.CATEGORY_NAME, 
									C.LFT
								  ORDER BY 
									C.LFT
								) ST
							WHERE 
								C.LFT BETWEEN P.LFT AND P.RGT
							AND   C.CATEGORY_ID = ST.CATEGORY_ID
							AND   P.CATEGORY_ID = ".$parentControllerId."
							AND ST.NODE_DEPTH = (COALESCE((SELECT 
																COUNT(C.CATEGORY_NAME) AS NODE_DEPTH
														  FROM 
																ls_category C, 
																ls_category P
														  WHERE 
																C.LFT BETWEEN P.LFT AND P.RGT
														  AND   C.CATEGORY_ID = ".$parentControllerId."),0) + 1)
						";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		
		public function getCategoryTable($id) {
			$select = "		SELECT C.CATEGORY_ID AS CATEGORY_ID,
							   C.LFT AS LFT,
							   C.RGT AS RGT,
							   C.ORDER_BY AS ORDER_BY,
							   C.CATEGORY_NAME AS CATEGORY_NAME,
							   C.DESCRIPTION AS DESCRIPTION,
							   (SELECT CAT.CATEGORY_ID
								  FROM ls_category CAT
								 WHERE C.LFT > CAT.LFT
								   AND C.RGT < CAT.RGT
								   AND CAT.LFT + 1 < CAT.RGT
								   AND CAT.LFT > 1) AS MOTHER_CATEGORY_ID,
							   (SELECT CATEGORY.CATEGORY_ID
								  FROM ls_category CATEGORY
								 WHERE CATEGORY.LFT = 1) AS FIRST_CATEGORY_ID
						  FROM ls_category C
						 WHERE C.CATEGORY_ID = ".$id."
						 LIMIT 1
						 ";
			$selectStatement 		= $this->tableGateway->getAdapter()->createStatement($select);
			$selectStatement->prepare();
			$selectStatementResult 	= $selectStatement->execute();
			
			if ($selectStatementResult instanceof ResultInterface && $selectStatementResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectStatementResult);
			}
			
			foreach($resultSet as $allNavs) {
				$catData[] = array(
							'CATEGORY_ID' 				=> $allNavs->CATEGORY_ID,
							'LFT' 						=> $allNavs->LFT,
							'RGT' 						=> $allNavs->RGT,
							'ORDER_BY' 					=> $allNavs->ORDER_BY,
							'CATEGORY_NAME' 			=> $allNavs->CATEGORY_NAME,
							'DESCRIPTION' 				=> $allNavs->DESCRIPTION,
							'MOTHER_CATEGORY_ID' 		=> $allNavs->MOTHER_CATEGORY_ID,
							'FIRST_CATEGORY_ID' 		=> $allNavs->FIRST_CATEGORY_ID,
						);
			}
			return $catData;
		}
		public function getCategoryInfoForEdit($id) {
			$select = "		SELECT C.CATEGORY_ID AS CATEGORY_ID,
							   C.LFT AS LFT,
							   C.RGT AS RGT,
							   C.ORDER_BY AS ORDER_BY,
							   C.CATEGORY_NAME AS CATEGORY_NAME,
							   C.DESCRIPTION AS DESCRIPTION,
							   (SELECT CATEGORY.CATEGORY_ID
								  FROM ls_category CATEGORY
								 WHERE CATEGORY.LFT = 1) AS FIRST_CATEGORY_ID,
								C.P_CODE,
								C.UNIT_CAL_IN,
								C.P_IMAGE,
								C.ACTIVE_INACTIVE
						  FROM ls_category C
						 WHERE C.CATEGORY_ID = ".$id."
						 LIMIT 1
						 ";
			$selectStatement 		= $this->tableGateway->getAdapter()->createStatement($select);
			$selectStatement->prepare();
			$selectStatementResult 	= $selectStatement->execute();
			
			if ($selectStatementResult instanceof ResultInterface && $selectStatementResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectStatementResult);
			}
			
			foreach($resultSet as $allNavs) {
				$catData[] = array(
							'CATEGORY_ID' 				=> $allNavs->CATEGORY_ID,
							'LFT' 						=> $allNavs->LFT,
							'RGT' 						=> $allNavs->RGT,
							'ORDER_BY' 					=> $allNavs->ORDER_BY,
							'CATEGORY_NAME' 			=> $allNavs->CATEGORY_NAME,
							'DESCRIPTION' 				=> $allNavs->DESCRIPTION,
							'FIRST_CATEGORY_ID' 		=> $allNavs->FIRST_CATEGORY_ID,
							'P_CODE'					=> $allNavs->P_CODE,
							'UNIT_CAL_IN'				=> $allNavs->UNIT_CAL_IN,
							'P_IMAGE'					=> $allNavs->P_IMAGE,
							'ACTIVE_INACTIVE'			=> $allNavs->ACTIVE_INACTIVE,
						);
			}
			return $catData;
		}
		public function categoryTableExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveEditCategory(Category $category) {
			$childData = array(
				'CATEGORY_NAME' 	=> $category->CATEGORY_NAME,
				'P_CODE' 			=> $category->P_CODE,
				'DESCRIPTION' 			=> $category->DESCRIPTION,
			);
			$id = $category->CATEGORY_ID;
			if($this->tableGateway->update($childData,array('CATEGORY_ID' => $id))){
				return true;
			} else {
				return false;
			}
			//echo "<pre>"; print_r($childData);die();
		}
		
		public function saveCategory(Category $category) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate1 		= date("Y-m-d", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			//echo "<pre>"; print_r($category);die();
			$NodeDepth 		= $category->NODEDEPTH;
			$LastLevel 		= $category->LAST_LEVEL;
			$P_COA_CODE 	= $category->P_COA_CODE;
			$p_cat_name 	= $category->P_CAT_NAME;
			
			$RECEIVABLE_COA_CODE = $P_COA_CODE;
			$CoaData 		= array();
			
			if($NodeDepth == 1){
				//Receivable Chart of Account Generate Start
				$maxReceiveableCOACode = '';
				$selectMaxRcvCOA = "SELECT 
											COALESCE(MAX(substr(COA_CODE,1,6)),305000)+1  AS MAX_RECEIVABLE_COA_CODE
										FROM
												gs_coa 		
										WHERE
											  substr(COA_CODE,1,6) BETWEEN 305000 AND 305999";
				$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
				$selectMaxRcvCOAStatement->prepare();
				$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
				
				if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($selectMaxRcvCOAResult);
				}
				
				foreach($resultSet as $resultMaxRcvCOA) {
					$maxReceiveableCOACode		= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
					$maxReceiveableCOACode		= $maxReceiveableCOACode."000";
				}
				//echo $maxReceiveableCOACode;die();	
				
				$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
				$RECEIVABLE_COA_NAME 	= "Investment In Product - ".$category->CATEGORY_NAME;
				$RECEIVABLE_AUTO_COA 	= 'y';			
				$marketWiseDivCOASql = "	
										SELECT 
												CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
												C.COA_ID AS RECEIVABLE_COA_ID,
												C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
										FROM 
												gs_coa C,
												c_company CN
										WHERE 
												C.COMPANY_ID  = CN.COMPANY_ID     
										AND   	C.COA_CODE  =  '305000000'
										ORDER BY 
												C.RGT		
									";
	
				$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
				$marketWiseDivCOA->prepare();
				$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
				if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
					$resultSet 			= new ResultSet();
					$resultSet->initialize($marketWiseDivCOAResult);
				}
				foreach($resultSet as $resultMaxDivCOA) {
					$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
					$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
					$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
				}
				//Receivable Chart of Account Generate End
				$CoaData = array(
							"COMPANY_ID"=>array(
													$RECEIVABLE_COMPANY_ID,
												),
							"PARENT_COA"=>array(
													$RECEIVABLE_COA_ID,
												),
							"CASH_FLOW_HEAD"=>array(
													$RECEIVABLE_CASH_FLOW_HEAD,
												),
							"COA_CODE"=>array(
													$RECEIVABLE_COA_CODE,
												),
							"COA_NAME"=>array(
													$RECEIVABLE_COA_NAME,
												),
							"AUTO_COA"=>array(
													$RECEIVABLE_AUTO_COA,
												),
						);
				
			}
			if($NodeDepth > 1){
				if($LastLevel == 'y'){
					//Receivable Chart of Account Generate Start
					$masPCC = $P_COA_CODE+999;
					
					$maxReceiveableCOACode = '';
					$selectMaxRcvCOA = "SELECT 
												COALESCE(MAX(substr(COA_CODE,1,9)),".$P_COA_CODE.")+1  AS MAX_RECEIVABLE_COA_CODE
											FROM
													gs_coa 		
											WHERE
												  substr(COA_CODE,1,9) BETWEEN ".$P_COA_CODE." AND ".$masPCC."";
					$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
					$selectMaxRcvCOAStatement->prepare();
					$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
					
					if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
						$resultSet = new ResultSet();
						$resultSet->initialize($selectMaxRcvCOAResult);
					}
					
					foreach($resultSet as $resultMaxRcvCOA) {
						$maxReceiveableCOACode		= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
						$maxReceiveableCOACode		= $maxReceiveableCOACode;
					}
					//echo $maxReceiveableCOACode;die();	
					
					$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
					$RECEIVABLE_COA_NAME 	= "Investment In Product - ".$p_cat_name." - ".$category->CATEGORY_NAME;
					$RECEIVABLE_AUTO_COA 	= 'y';			
					$marketWiseDivCOASql = "	
											SELECT 
													CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
													C.COA_ID AS RECEIVABLE_COA_ID,
													C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
											FROM 
													gs_coa C,
													c_company CN
											WHERE 
													C.COMPANY_ID  = CN.COMPANY_ID     
											AND   	C.COA_CODE  =  '305000000'
											ORDER BY 
													C.RGT		
										";
		
					$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
					$marketWiseDivCOA->prepare();
					$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
					if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
						$resultSet 			= new ResultSet();
						$resultSet->initialize($marketWiseDivCOAResult);
					}
					foreach($resultSet as $resultMaxDivCOA) {
						$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
						$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
						$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
					}
					//Receivable Chart of Account Generate End
					
					$CoaData = array(
							"COMPANY_ID"=>array(
													$RECEIVABLE_COMPANY_ID,
												),
							"PARENT_COA"=>array(
													$RECEIVABLE_COA_ID,
												),
							"CASH_FLOW_HEAD"=>array(
													$RECEIVABLE_CASH_FLOW_HEAD,
												),
							"COA_CODE"=>array(
													$RECEIVABLE_COA_CODE,
												),
							"COA_NAME"=>array(
													$RECEIVABLE_COA_NAME,
												),
							"AUTO_COA"=>array(
													$RECEIVABLE_AUTO_COA,
												),
						);
				}
			}
			
			//echo "<pre>"; print_r($CoaData); die();
			
			//die();
			$updateParentData 	= array();
			$newChildLft		= '';
			$newChildRgt		= '';
			if($category->PARENT_CATEGORY > 0) {
				$categoryDetails 	= $this->getCategoryTable($category->PARENT_CATEGORY);		
				//echo "<pre>"; print_r($categoryDetails);die();
				$rgt 				= $categoryDetails[0]['RGT'];
				$newChildLft		= $rgt;
				$newChildRgt		= $rgt + 1;
			} else {
				$newChildLft		= 1;
				$newChildRgt		= 2;
			}
			
			$childData = array(
				'CATEGORY_NAME' 	=> $category->CATEGORY_NAME,
				'COA_CODE' 			=> $RECEIVABLE_COA_CODE,
				'ORDER_BY' 			=> $category->ORDER_BY,
				'DESCRIPTION' 		=> $category->DESCRIPTION,
				'LFT' 				=> $newChildLft,
				'RGT' 				=> $newChildRgt,
				'ACTIVE_INACTIVE' 	=> 'yes',				
				'P_CODE'			=> $category->P_CODE,
				'UNIT_CAL_IN' 		=> $category->UNIT_CAL_IN,
				'P_IMAGE' 			=> $category->P_IMAGE,				
				'BUSINESS_DATE' 	=> $businessDate1,
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
			);
			//echo "<pre>"; print_r($childData);die();
			$existCheckData = array(
				'CATEGORY_NAME' 	=> $category->CATEGORY_NAME,
				'LFT' 				=> $newChildLft,
				'RGT' 				=> $newChildRgt,
			);
			$id = (int) $category->CATEGORY_ID;
			//echo $id;die(); 
			if($id == 0) {
				if($this->categoryTableExist($existCheckData)) {
					throw new \Exception("System Category ".$category->CATEGORY_NAME." already exist!");
				} else {
					if($category->PARENT_CATEGORY > 0){
						$whereRight = array(
							'RGT >= ?' => $rgt,
						);
						$whereLeft = array(
							'LFT > ?' => $rgt,
						);
						
						if($this->tableGateway->update(array('RGT' => new \Zend\Db\Sql\Expression('RGT + 2')),$whereRight)) {
							if($this->tableGateway->update(array('LFT' => new \Zend\Db\Sql\Expression('LFT + 2')),$whereLeft)) {
								if($this->tableGateway->insert($childData)) {
									$catExistSql = $this->categoryTableExist($existCheckData);
									$catId = (int) $catExistSql->CATEGORY_ID;
									$returnData	= array(
												"CAT_ID" => $catId,
												"COA_DATA" => $CoaData,
										  );
									return $returnData;
								} else {
									return false;
								}
							} else {
								if($this->tableGateway->insert($childData)) {
									$catExistSql = $this->categoryTableExist($existCheckData);
									$catId = (int) $catExistSql->CATEGORY_ID;
									$returnData	= array(
												"CAT_ID" => $catId,
												"COA_DATA" => $CoaData,
										  );
									return $returnData;
								} else {
									return false;
								}
							}
						} else {
							return false;
						}
					}
				}
			} else {
				$childData1 = array(
					'CATEGORY_NAME'=> $category->CATEGORY_NAME,
					'DESCRIPTION'=> $category->DESCRIPTION,
				);
				if($this->tableGateway->update($childData1,array('CATEGORY_ID' => $id))){
					return true;
				} else {
					return false;
				}
			}
		}
		
		public function insertCategoryCSVData($table, $tbl_f, $csv_values, $categoryCounter){
			$NodeDepth = 0;
			$LastLevel = 'n';
			$P_COA_CODE = '';
			$p_cat_name = '';
			$underModel = '';
			//$addPrice = '';
			//$buyPrice = 0.00;
			//$salePrice = 0.00;
			
			//echo'<pre>';print_r(explode(",",$tbl_f));//die();
			//echo '<pre>';print_r(explode(",",$csv_values));die();
			$tbl_f_temp = explode(",",$tbl_f);
			$csv_values_temp = explode(",",$csv_values);
			//echo '<pre>';print_r($tbl_f_temp);
			$unusedColumn = array();
			$unusedColumnValue = array();
			//echo sizeof($tbl_f_temp);
			for($i = 0; $i < sizeof($tbl_f_temp); $i++){
				if($tbl_f_temp[$i] == 'LAST_LEVEL'){
					$LastLevel = str_replace("'","",strtolower($csv_values_temp[$i]));
					$unusedColumn[] = $tbl_f_temp[$i];
					$unusedColumnValue[] = $csv_values_temp[$i];
				} else if($tbl_f_temp[$i] == 'NODE_DEPTH'){
					$NodeDepth = str_replace("'","",$csv_values_temp[$i]);
					$unusedColumn[] = $tbl_f_temp[$i];
					$unusedColumnValue[] = $csv_values_temp[$i];
				} else if($tbl_f_temp[$i] == 'P_COA_CODE'){
					$P_COA_CODE = str_replace("'","",$csv_values_temp[$i]);
					$unusedColumn[] = $tbl_f_temp[$i];
					$unusedColumnValue[] = $csv_values_temp[$i];
					if($categoryCounter == 1){
						array_push($tbl_f_temp,"COA_CODE");
						array_push($csv_values_temp,$P_COA_CODE);
					}
				} else if($tbl_f_temp[$i] == 'UNDER_MODEL'){
					$underModel = str_replace("'","",$csv_values_temp[$i]);
					$unusedColumn[] = $tbl_f_temp[$i];
					$unusedColumnValue[] = $csv_values_temp[$i];
				} else {
					$tblFieldOrganize = implode(',',array_diff($tbl_f_temp,$unusedColumn));
					$csvValuesOrganize = implode(',',array_diff($csv_values_temp,$unusedColumnValue));
				}
			}
			//echo '<pre>';print_r($tblFieldOrganize);//die();
			//echo '<pre>';print_r($csvValuesOrganize);die();
			
			
			if($categoryCounter == 1){
				if($this->tableGateway->update(array('RGT' => new \Zend\Db\Sql\Expression('RGT + 2')),1)) {
					$insertCSVSql = "INSERT INTO $table ({$tblFieldOrganize}) VALUES({$csvValuesOrganize})";
					$insertCSVStatement = $this->tableGateway->getAdapter()->createStatement($insertCSVSql);
					$insertCSVStatement->prepare();
					if(!$insertCSVStatement->execute()) {
						return false;
					} else {				
						$RECEIVABLE_COA_CODE = $P_COA_CODE;
						$CoaData = array();
						$maxCategoryID  = '';
						$maxCATIDSql = "SELECT MAX(ls_category.CATEGORY_ID) AS CATEGORY_ID FROM ls_category";
						$maxCATIDStatement = $this->tableGateway->getAdapter()->createStatement($maxCATIDSql);
						$maxCATIDStatement->prepare();
						$maxCATIDResult = $maxCATIDStatement->execute();
						if ($maxCATIDResult instanceof ResultInterface && $maxCATIDResult->isQueryResult()) {
							$resultSet = new ResultSet();
							$resultSet->initialize($maxCATIDResult);
						}
						foreach($resultSet as $resultMaxCATID) {
							$maxCategoryID	= $resultMaxCATID->CATEGORY_ID;
						}
						$maxCategoryName= '';
						$maxCategoryNameSql = "SELECT ls_category.CATEGORY_NAME AS NAME FROM ls_category WHERE CATEGORY_ID = {$maxCategoryID}";
						$maxCategoryNameStatement = $this->tableGateway->getAdapter()->createStatement($maxCategoryNameSql);
						$maxCategoryNameStatement->prepare();
						$maxCategoryNameResult = $maxCategoryNameStatement->execute();
						if ($maxCategoryNameResult instanceof ResultInterface && $maxCategoryNameResult->isQueryResult()) {
							$resultSet = new ResultSet();
							$resultSet->initialize($maxCategoryNameResult);
						}
						foreach($resultSet as $resultMaxCategoryName) {
							$maxCategoryName	= $resultMaxCategoryName->NAME;
						}
						if($NodeDepth == 1){
							//Receivable Chart of Account Generate Start
							$maxReceiveableCOACode = '';
							$selectMaxRcvCOA = "SELECT 
														COALESCE(MAX(substr(COA_CODE,1,6)),305000)+1  AS MAX_RECEIVABLE_COA_CODE
													FROM
														gs_coa 		
													WHERE
														  substr(COA_CODE,1,6) BETWEEN 305000 AND 305999";
							$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
							$selectMaxRcvCOAStatement->prepare();
							$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
							
							if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
								$resultSet = new ResultSet();
								$resultSet->initialize($selectMaxRcvCOAResult);
							}
							
							foreach($resultSet as $resultMaxRcvCOA) {
								$maxReceiveableCOACode		= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
								$maxReceiveableCOACode		= $maxReceiveableCOACode."000";
							}
							//echo $maxReceiveableCOACode;die();	
							
							$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
							$RECEIVABLE_COA_NAME 	= "Investment In Product - ".$maxCategoryName;
							$RECEIVABLE_AUTO_COA 	= 'y';			
							$marketWiseDivCOASql = "	
													SELECT 
															CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
															C.COA_ID AS RECEIVABLE_COA_ID,
															C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
													FROM 
															gs_coa C,
															c_company CN
													WHERE 
															C.COMPANY_ID  = CN.COMPANY_ID     
													AND   	C.COA_CODE  =  '305000000'
													ORDER BY 
															C.RGT		
												";
				
							$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
							$marketWiseDivCOA->prepare();
							$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
							if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
								$resultSet 			= new ResultSet();
								$resultSet->initialize($marketWiseDivCOAResult);
							}
							foreach($resultSet as $resultMaxDivCOA) {
								$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
								$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
								$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
							}
							//Receivable Chart of Account Generate End
							$CoaData = array(
										"COMPANY_ID"=>array(
																$RECEIVABLE_COMPANY_ID,
															),
										"PARENT_COA"=>array(
																$RECEIVABLE_COA_ID,
															),
										"CASH_FLOW_HEAD"=>array(
																$RECEIVABLE_CASH_FLOW_HEAD,
															),
										"COA_CODE"=>array(
																$RECEIVABLE_COA_CODE,
															),
										"COA_NAME"=>array(
																$RECEIVABLE_COA_NAME,
															),
										"AUTO_COA"=>array(
																$RECEIVABLE_AUTO_COA,
															),
									);
							
						}
						if($NodeDepth > 1){
							if(strtolower($LastLevel) == 'y'){
								//Receivable Chart of Account Generate Start
								$masPCC = $P_COA_CODE+999;
								$maxReceiveableCOACode = '';
								$selectMaxRcvCOA = "SELECT 
															COALESCE(MAX(substr(COA_CODE,1,9)),".$P_COA_CODE.")+1  AS MAX_RECEIVABLE_COA_CODE
														FROM
																gs_coa 		
														WHERE
															  substr(COA_CODE,1,9) BETWEEN ".$P_COA_CODE." AND ".$masPCC."";
								$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
								$selectMaxRcvCOAStatement->prepare();
								$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
								
								if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
									$resultSet = new ResultSet();
									$resultSet->initialize($selectMaxRcvCOAResult);
								}
								
								foreach($resultSet as $resultMaxRcvCOA) {
									$maxReceiveableCOACode		= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
									$maxReceiveableCOACode		= $maxReceiveableCOACode;
								}
								//echo $maxReceiveableCOACode;die();	
								
								$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
								$RECEIVABLE_COA_NAME 	= "Investment In Product - ".$p_cat_name." - ".$maxCategoryName;
								$RECEIVABLE_AUTO_COA 	= 'y';			
								$marketWiseDivCOASql = "	
														SELECT 
																CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
																C.COA_ID AS RECEIVABLE_COA_ID,
																C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
														FROM 
																gs_coa C,
																c_company CN
														WHERE 
																C.COMPANY_ID  = CN.COMPANY_ID     
														AND   	C.COA_CODE  =  '305000000'
														ORDER BY 
																C.RGT		
													";
					
								$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
								$marketWiseDivCOA->prepare();
								$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
								if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
									$resultSet 			= new ResultSet();
									$resultSet->initialize($marketWiseDivCOAResult);
								}
								foreach($resultSet as $resultMaxDivCOA) {
									$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
									$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
									$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
								}
								//Receivable Chart of Account Generate End
								
								$CoaData = array(
													"COMPANY_ID"=>array(
																			$RECEIVABLE_COMPANY_ID,
																		),
													"PARENT_COA"=>array(
																			$RECEIVABLE_COA_ID,
																		),
													"CASH_FLOW_HEAD"=>array(
																			$RECEIVABLE_CASH_FLOW_HEAD,
																		),
													"COA_CODE"=>array(
																			$RECEIVABLE_COA_CODE,
																		),
													"COA_NAME"=>array(
																			$RECEIVABLE_COA_NAME,
																		),
													"AUTO_COA"=>array(
																			$RECEIVABLE_AUTO_COA,
																		),
												);
							}
						}
						//echo '<pre>';print_r($CoaData);die();
						$returnData	= array(
									"CAT_ID" => $maxCategoryID,
									"COA_DATA" => $CoaData,
									"ADD_PRICE" => '',
									"BUY_PRICE" => '',
									"SALE_PRICE" => ''
							  );
						return $returnData;
					}
				}
			} else {
				$rgt = '';
				$maxCATIDSql = "SELECT * FROM ls_category where LOWER(ls_category.CATEGORY_NAME) = '".strtolower($underModel)."'";
				$maxCATIDStatement = $this->tableGateway->getAdapter()->createStatement($maxCATIDSql);
				$maxCATIDStatement->prepare();
				$maxCATIDResult = $maxCATIDStatement->execute();
				if ($maxCATIDResult instanceof ResultInterface && $maxCATIDResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($maxCATIDResult);
				}
				foreach($resultSet as $resultMaxCATID) {
					$coaCode	= $resultMaxCATID->COA_CODE;
					$lft	= $resultMaxCATID->LFT;
					$rgt	= $resultMaxCATID->RGT;
				}
				$newChildLft		= $rgt;
				$newChildRgt		= $rgt + 1;
				$whereRight = array(
					'RGT >= ?' => $rgt,
				);
				$whereLeft = array(
					'LFT > ?' => $rgt,
				);
				if($this->tableGateway->update(array('RGT' => new \Zend\Db\Sql\Expression('RGT + 2')),$whereRight)) {
					$this->tableGateway->update(array('LFT' => new \Zend\Db\Sql\Expression('LFT + 2')),$whereLeft);
					
					$tbl_f_temp = explode(",",$tblFieldOrganize);
					$csv_values_temp = explode(",",$csvValuesOrganize);
					
					$addPrice = $csv_values_temp[6];
					$buyPrice = $csv_values_temp[7];
					$salePrice = $csv_values_temp[8];
					
					
					unset($tbl_f_temp[6],$tbl_f_temp[7],$tbl_f_temp[8]);
					unset($csv_values_temp[6],$csv_values_temp[7],$csv_values_temp[8]);
					
					$P_COA_CODE = "'".$P_COA_CODE."'";
					array_push($tbl_f_temp, "LFT", "RGT","COA_CODE");
					array_push($csv_values_temp, $newChildLft,$newChildRgt,$P_COA_CODE);
					
					$tblFieldOrganize = implode(',',$tbl_f_temp);
					$csvValuesOrganize = implode(',',$csv_values_temp);
					
					//echo '<pre>';print_r($tblFieldOrganize);//die();
					//echo '<pre>';print_r($csvValuesOrganize);die();
					
					$insertCSVDataSql = "INSERT INTO $table ({$tblFieldOrganize}) VALUES({$csvValuesOrganize})";
					$insertCSVDataStatement = $this->tableGateway->getAdapter()->createStatement($insertCSVDataSql);
					$insertCSVDataStatement->prepare();
					if(!$insertCSVDataStatement->execute()) {
						return false;
					} else {
						//return 1;
						$RECEIVABLE_COA_CODE = $P_COA_CODE;
						$CoaData = array();
						$maxCategoryID  = '';
						$maxCATIDSql = "SELECT MAX(ls_category.CATEGORY_ID) AS CATEGORY_ID FROM ls_category";
						$maxCATIDStatement = $this->tableGateway->getAdapter()->createStatement($maxCATIDSql);
						$maxCATIDStatement->prepare();
						$maxCATIDResult = $maxCATIDStatement->execute();
						if ($maxCATIDResult instanceof ResultInterface && $maxCATIDResult->isQueryResult()) {
							$resultSet = new ResultSet();
							$resultSet->initialize($maxCATIDResult);
						}
						foreach($resultSet as $resultMaxCATID) {
							$maxCategoryID	= $resultMaxCATID->CATEGORY_ID;
						}					
						//echo $maxRetailerID;die();
						$maxCategoryName= '';
						$maxCategoryNameSql = "SELECT ls_category.CATEGORY_NAME AS NAME,COA_CODE AS COACODE FROM ls_category WHERE CATEGORY_ID = {$maxCategoryID}";
						$maxCategoryNameStatement = $this->tableGateway->getAdapter()->createStatement($maxCategoryNameSql);
						$maxCategoryNameStatement->prepare();
						$maxCategoryNameResult = $maxCategoryNameStatement->execute();
						if ($maxCategoryNameResult instanceof ResultInterface && $maxCategoryNameResult->isQueryResult()) {
							$resultSet = new ResultSet();
							$resultSet->initialize($maxCategoryNameResult);
						}
						foreach($resultSet as $resultMaxCategoryName) {
							$maxCategoryName	= $resultMaxCategoryName->NAME;
							$maxCategoryCoaCode	= $resultMaxCategoryName->COACODE;
						}
						if($NodeDepth == 1){
							//Receivable Chart of Account Generate Start
							$maxReceiveableCOACode = '';
							$selectMaxRcvCOA = "SELECT 
														COALESCE(MAX(substr(COA_CODE,1,6)),305000)+1  AS MAX_RECEIVABLE_COA_CODE
													FROM
														gs_coa 		
													WHERE
														  substr(COA_CODE,1,6) BETWEEN 305000 AND 305999";
							$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
							$selectMaxRcvCOAStatement->prepare();
							$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
							
							if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
								$resultSet = new ResultSet();
								$resultSet->initialize($selectMaxRcvCOAResult);
							}
							
							foreach($resultSet as $resultMaxRcvCOA) {
								$maxReceiveableCOACode		= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
								$maxReceiveableCOACode		= $maxReceiveableCOACode."000";
							}
							//echo $maxReceiveableCOACode;die();	
							
							$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
							$RECEIVABLE_COA_NAME 	= "Investment In Product - ".$maxCategoryName;
							$RECEIVABLE_AUTO_COA 	= 'y';			
							$marketWiseDivCOASql = "	
													SELECT 
															CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
															C.COA_ID AS RECEIVABLE_COA_ID,
															C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
													FROM 
															gs_coa C,
															c_company CN
													WHERE 
															C.COMPANY_ID  = CN.COMPANY_ID     
													AND   	C.COA_CODE  =  '305000000'
													ORDER BY 
															C.RGT		
												";
				
							$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
							$marketWiseDivCOA->prepare();
							$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
							if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
								$resultSet 			= new ResultSet();
								$resultSet->initialize($marketWiseDivCOAResult);
							}
							foreach($resultSet as $resultMaxDivCOA) {
								$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
								$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
								$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
							}
							//Receivable Chart of Account Generate End
							$CoaData = array(
										"COMPANY_ID"=>array(
																$RECEIVABLE_COMPANY_ID,
															),
										"PARENT_COA"=>array(
																$RECEIVABLE_COA_ID,
															),
										"CASH_FLOW_HEAD"=>array(
																$RECEIVABLE_CASH_FLOW_HEAD,
															),
										"COA_CODE"=>array(
																$RECEIVABLE_COA_CODE,
															),
										"COA_NAME"=>array(
																$RECEIVABLE_COA_NAME,
															),
										"AUTO_COA"=>array(
																$RECEIVABLE_AUTO_COA,
															),
									);
							
						}
						if($NodeDepth > 1){
							if(strtolower($LastLevel) == 'y'){
								$p_cat_name = $underModel;
								//Receivable Chart of Account Generate Start
								$masPCC = $P_COA_CODE+999;
								$maxReceiveableCOACode = '';
								$selectMaxRcvCOA = "SELECT 
															COALESCE(MAX(substr(COA_CODE,1,9)),".$P_COA_CODE.") AS MAX_RECEIVABLE_COA_CODE
														FROM
																gs_coa 		
														WHERE
															  substr(COA_CODE,1,9) BETWEEN ".$P_COA_CODE." AND ".$masPCC."";
								$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
								$selectMaxRcvCOAStatement->prepare();
								$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
								
								if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
									$resultSet = new ResultSet();
									$resultSet->initialize($selectMaxRcvCOAResult);
								}
								
								foreach($resultSet as $resultMaxRcvCOA) {
									$maxReceiveableCOACode		= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
									$maxReceiveableCOACode		= $maxReceiveableCOACode;
								}
								//echo $maxReceiveableCOACode;die();	
								
								$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
								$RECEIVABLE_COA_NAME 	= "Investment In Product - ".$p_cat_name." - ".$maxCategoryName;
								$RECEIVABLE_AUTO_COA 	= 'y';			
								$marketWiseDivCOASql = "	
														SELECT 
																CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
																C.COA_ID AS RECEIVABLE_COA_ID,
																C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
														FROM 
																gs_coa C,
																c_company CN
														WHERE 
																C.COMPANY_ID  = CN.COMPANY_ID     
														AND   	C.COA_CODE  =  '305000000'
														ORDER BY 
																C.RGT		
													";
					
								$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
								$marketWiseDivCOA->prepare();
								$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
								if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
									$resultSet 			= new ResultSet();
									$resultSet->initialize($marketWiseDivCOAResult);
								}
								foreach($resultSet as $resultMaxDivCOA) {
									$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
									$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
									$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
								}
								//Receivable Chart of Account Generate End
								
								$CoaData = array(
													"COMPANY_ID"=>array(
																			$RECEIVABLE_COMPANY_ID,
																		),
													"PARENT_COA"=>array(
																			$RECEIVABLE_COA_ID,
																		),
													"CASH_FLOW_HEAD"=>array(
																			$RECEIVABLE_CASH_FLOW_HEAD,
																		),
													"COA_CODE"=>array(
																			$RECEIVABLE_COA_CODE,
																		),
													"COA_NAME"=>array(
																			$RECEIVABLE_COA_NAME,
																		),
													"AUTO_COA"=>array(
																			$RECEIVABLE_AUTO_COA,
																		),
												);
							}
						}
						$returnData	= array(
												"CAT_ID" => $maxCategoryID,
												"COA_DATA" => $CoaData,
												"ADD_PRICE" => $addPrice,
												"BUY_PRICE" => $buyPrice,
												"SALE_PRICE" => $salePrice
										  );
						return $returnData;
					}
				}
			}
			
		}
		public function saveCategoryPriceFromCSV($catID,$buyPrice,$salePrice){
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate1 		= date("Y-m-d", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;			
			$priceInsertStatus = 0;
			$insertPriceSql = "INSERT INTO ls_cat_price (CATEGORY_ID,BUY_PRICE,SALE_PRICE,START_DATE,BUSINESS_DATE,OPERATE_BY) VALUES (".$catID.",".$buyPrice.",".$salePrice.",'".$businessDate1."','".$businessDate1."',".$userId.")";
			$insertPriceStatement = $this->tableGateway->getAdapter()->createStatement($insertPriceSql);
			$insertPriceStatement->prepare();
			if(!$insertPriceStatement->execute()){
				$priceInsertStatus = 0;
			} else {
				$priceInsertStatus = 1;
			}
			return $priceInsertStatus;
		}
		public function categoryCounter(){
			$totalCount = 0;
			$maxCATIDSql = "SELECT COUNT(CATEGORY_ID) AS TOTALCOUNT FROM ls_category";
			$maxCATIDStatement = $this->tableGateway->getAdapter()->createStatement($maxCATIDSql);
			$maxCATIDStatement->prepare();
			$maxCATIDResult = $maxCATIDStatement->execute();
			if ($maxCATIDResult instanceof ResultInterface && $maxCATIDResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($maxCATIDResult);
			}
			foreach($resultSet as $resultMaxCATID) {
				$totalCount	= $resultMaxCATID->TOTALCOUNT;
			}
			return $totalCount;
		}
		public function deleteCategory($id) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$updateParentData 	= array();
			$newChildLft		= '';
			$newChildRgt		= '';
			
			$categoryDetails 	= $this->getCategoryTable($id);
			$lft 				= $categoryDetails[0]['LFT'];
			$rgt 				= $categoryDetails[0]['RGT'];
			//echo "<pre>"; print_r($categoryDetails);die();
			if($rgt == ($lft+1)){
				if($this->deleteCategoryTable($id)) {
					$whereRight = array(
						'RGT >= ?' => $rgt,
					);
					
					$whereLeft = array(
						'LFT > ?' => $lft,
					);
					if($this->tableGateway->update(array('RGT' => new \Zend\Db\Sql\Expression('RGT - 2')),$whereRight)){
						$this->tableGateway->update(array('LFT' => new \Zend\Db\Sql\Expression('LFT - 2')),$whereLeft);
						return true;
					} else {
						return false;
					}
				} else {
					
					return false;
				}
			} else {
				return false;
			}
		}
		
		public function getCheckChild($lft,$rgt) {
			$CATEGORY_ID			=	array();
			echo $selectDeletedNavId		= "
										SELECT 
												CATEGORY_ID
										FROM	
												ls_category
										WHERE
												LFT BETWEEN ".$lft." AND ".$rgt."
										AND		CATEGORY_ID NOT IN (
												SELECT 
														SYSNAV.CATEGORY_ID
												FROM	
														ls_category			SYSNAV,
														CATEGORY_OPERATION	SYSNAVOPR,
														S_ROLE_OPERATION 		ROLEOPR
												WHERE
														SYSNAV.LFT BETWEEN ".$lft." AND ".$rgt."
												AND		SYSNAV.CATEGORY_ID				= SYSNAVOPR.CATEGORY_ID
												AND		SYSNAVOPR.SYSTEM_NAV_OPERATION_ID	= ROLEOPR.SYSTEM_NAV_OPERATION_ID	
										)
			";die();
			$deletedNavIdStatement	= $this->tableGateway->getAdapter()->createStatement($selectDeletedNavId);
			$deletedNavIdStatement->prepare();
			$deletedNavIdResult		= $deletedNavIdStatement->execute();
			if ($deletedNavIdResult instanceof ResultInterface && $deletedNavIdResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($deletedNavIdResult);
			}
			return $resultSet;
		}
		
		public function deleteCategoryTable($id) {
			if($this->tableGateway->delete(array('CATEGORY_ID' => $id))){
				return true;
			} else {
				return false;
			}
		}
		
		public function transectionStart() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
		}
		
		public function transectionEnd() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->commit();
		}
		
		public function transectionInterrupted() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->rollback();
		}
		
	}
?>	