<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	class SuppWiseCategoryTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		/*public function fetchAll() {
			//echo 'hi htere';die();
			$resultSet = $this->tableGateway->select();
			return $resultSet;
		}*/
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('ls_supp_wise_category');
				$select->join('ls_category','ls_category.CATEGORY_ID = ls_supp_wise_category.CATEGORY_ID','CATEGORY_NAME');
				$select->join('ls_supplier_info','ls_supplier_info.SUPPLIER_INFO_ID = ls_supp_wise_category.SUPPLIER_INFO_ID','NAME');
				$select->where("END_DATE IS NULL");
				$select->order('CATEGORY_NAME ASC');
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new SuppWiseCategory());
				// create a new pagination adapter object
				$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				return $paginator;
			}
		}
		
		public function categoryTableView() {
			$select = "		SELECT 
								  	C.CATEGORY_ID, 
								  	rpad(' ',COUNT(C.CATEGORY_NAME)*6,'-') AS CDOT,
									C.CATEGORY_NAME AS CATEGORY_NAME,
								  	C.LFT,
								  	C.ORDER_BY,
								  	COUNT(C.CATEGORY_NAME) AS NODE_DEPTH,
									C.ACTIVE_INACTIVE AS ACTIVE_INACTIVE
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
		
		public function getSupplierWiseCategoryTable($id) {
			$select = "SELECT suppcat.SUPP_WISE_CATEGORY_ID, supp.NAME, cat.CATEGORY_NAME, suppcat.IS_SUPPLY,suppcat.CATEGORY_ID, suppcat.SUPPLIER_INFO_ID
						FROM ls_supp_wise_category suppcat, ls_supplier_info supp, ls_category cat
						WHERE suppcat.SUPP_WISE_CATEGORY_ID = ".$id."
						AND suppcat.SUPPLIER_INFO_ID = supp.SUPPLIER_INFO_ID
						AND cat.CATEGORY_ID = suppcat.CATEGORY_ID
						";
			$selectStatement 		= $this->tableGateway->getAdapter()->createStatement($select);
			$selectStatement->prepare();
			$selectStatementResult 	= $selectStatement->execute();
			if ($selectStatementResult instanceof ResultInterface && $selectStatementResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectStatementResult);
			}
			return $resultSet;
		}
		
		public function categorySuppWiseTableExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveEditSuppWiseCategory(SuppWiseCategory $suppwisecategory) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate1 		= date("Y-m-d", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$data = array('END_DATE' => $businessDate1);
			$id = $suppwisecategory->SUPP_WISE_CATEGORY_ID;
			if($this->tableGateway->update($data,array('SUPP_WISE_CATEGORY_ID' => $id))){
				$dataForInsert = array(
										'CATEGORY_ID' 		=> $suppwisecategory->CATEGORY_ID,
										'SUPPLIER_INFO_ID' 	=> $suppwisecategory->SUPPLIER_INFO_ID,
										'START_DATE' 		=> $businessDate1,
										'IS_SUPPLY' 		=> $suppwisecategory->IS_SUPPLY,
										'BUSINESS_DATE' 	=> $businessDate1,
										'RECORD_DATE' 		=> $recDate,
										'OPERATE_BY' 		=> $userId,
									);
				if($this->tableGateway->insert($dataForInsert)) {
					return true;
				} else {
					return false;	
				}
			} else {
				return false;
			}
			//echo "<pre>"; print_r($childData);die();
		}
		
		public function saveSuppWiseCategory(SuppWiseCategory $suppwisecategory) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate1 		= date("Y-m-d", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			//echo "<pre>"; print_r($suppwisecategory);die();
			$data = array(
				'CATEGORY_ID' 		=> $suppwisecategory->CATEGORY_ID,
				'SUPPLIER_INFO_ID' 	=> $suppwisecategory->SUPPLIER_INFO_ID,
				'START_DATE' 		=> $businessDate1,
				'IS_SUPPLY' 		=> $suppwisecategory->IS_SUPPLY,
				'BUSINESS_DATE' 	=> $businessDate1,
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
			);
			$existCheckData = array(
				'CATEGORY_ID'		=> $suppwisecategory->CATEGORY_ID,
				'SUPPLIER_INFO_ID'  => $suppwisecategory->SUPPLIER_INFO_ID,
				'END_DATE IS NULL',
			);
			//echo "<pre>"; print_r($data);die();
			
			if($catSuppInfo = $this->categorySuppWiseTableExist($existCheckData)) {
				$csId =  $catSuppInfo->SUPP_WISE_CATEGORY_ID;
				$data1['END_DATE'] 	= $businessDate;
				if($this->tableGateway->update($data1,array('SUPP_WISE_CATEGORY_ID' => $csId))){
					if($this->tableGateway->insert($data)) {
						return true;
					} else {
						return false;
					}
				}
			} else {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;
				}
			}
		}
		
		public function deleteCategoryUnderSupplier($id) {
			//echo $id;die();
			if($this->tableGateway->delete(array('SUPP_WISE_CATEGORY_ID' => $id))) {
				return true;
			} else {
				return false;
			}
		}
		public function getDistinctSuppInfo() {
			$select = "		
						SELECT 
								DISTINCT suppcat.SUPPLIER_INFO_ID,supp.NAME
						FROM 
								ls_supp_wise_category suppcat, ls_supplier_info supp
						WHERE   suppcat.SUPPLIER_INFO_ID = supp.SUPPLIER_INFO_ID
						ORDER BY 
								NAME ASC
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
		public function fetchSuppWiseProductStatus($cond,$cond1) {
			$instrumentDetailsSql = "
									SELECT suppcat.SUPP_WISE_CATEGORY_ID, supp.NAME, cat.CATEGORY_NAME, suppcat.IS_SUPPLY,suppcat.CATEGORY_ID, suppcat.SUPPLIER_INFO_ID,suppcat.START_DATE, suppcat.END_DATE
									FROM ls_supp_wise_category suppcat, ls_supplier_info supp, ls_category cat
									WHERE suppcat.SUPPLIER_INFO_ID = supp.SUPPLIER_INFO_ID
									AND cat.CATEGORY_ID = suppcat.CATEGORY_ID
									{$cond}
									{$cond1}
									ORDER BY cat.CATEGORY_NAME ASC
									";
			$instrumentDetails = $this->tableGateway->getAdapter()->createStatement($instrumentDetailsSql);
			$instrumentDetails->prepare();
			$instrumentDetailsResult = $instrumentDetails->execute();
			
			if ($instrumentDetailsResult instanceof ResultInterface && $instrumentDetailsResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($instrumentDetailsResult);
			}
			$productDataDetails = array();
			foreach($resultSet as $productDatas) {
				$productDataDetails['SUPP_WISE_CATEGORY_ID'][] 	= $productDatas["SUPP_WISE_CATEGORY_ID"];
				$productDataDetails['NAME'][] 					= $productDatas["NAME"];
				$productDataDetails['CATEGORY_NAME'][] 			= $productDatas["CATEGORY_NAME"];
				$productDataDetails['IS_SUPPLY'][] 				= $productDatas["IS_SUPPLY"];
				$productDataDetails['CATEGORY_ID'][] 			= $productDatas["CATEGORY_ID"];
				$productDataDetails['SUPPLIER_INFO_ID'][] 		= $productDatas["SUPPLIER_INFO_ID"];
				$productDataDetails['START_DATE'][] 			= date('d-m-Y',strtotime($productDatas["START_DATE"]));
				$productDataDetails['END_DATE'][] 				= $productDatas["END_DATE"];
			}
			return $productDataDetails;
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