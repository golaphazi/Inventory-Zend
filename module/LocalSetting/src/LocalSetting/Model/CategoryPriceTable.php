<?php
	namespace LocalSetting\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Session\Container as SessionContainer;
	
	
	class CategoryPriceTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		function check_injection($target) {
			$target 	 = strtolower($target);
			$constraints = array('|',',','*',' ','"',"'",'<','>','!^','^','!','$','&','*','#','+','=','|',"%",'(',')','{','}','[',']','~','`',';',':','select','delete','update','insertinto','set','values','mysql_query');
			for($i=0;$i<sizeof($constraints);$i++) {
				$target = str_replace($constraints[$i],'',$target);
			}
			return $target;
		}
		public function fetchCategoryModelName($input) {
			if(!empty($input)){
				$getTblDataSql   = "SELECT CATEGORY_ID, CATEGORY_NAME FROM ls_category WHERE LOWER( CATEGORY_NAME ) = '".strtolower($input)."' AND active_inactive = 'yes' ORDER BY CATEGORY_NAME DESC";
			}else{
				$getTblDataSql   = "SELECT CATEGORY_ID, CATEGORY_NAME FROM ls_category WHERE  active_inactive = 'yes' ORDER BY CATEGORY_NAME ASC";
			}
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
				
			}
			return $resultSet;
		}
		
		public function fetchAll() {
			$marketDetailsSql = "	
									SELECT 
											CP.CAT_PRICE_ID, 
											CP.BUY_PRICE,
											CP.SALE_PRICE,
											C.CATEGORY_NAME
									FROM 
											ls_cat_price 	CP,
											ls_category 	C
									WHERE
											CP.CATEGORY_ID = C.CATEGORY_ID
											AND CP.END_DATE IS NULL
									ORDER BY 
											C.CATEGORY_NAME ASC	
								";
			$marketDetails = $this->tableGateway->getAdapter()->createStatement($marketDetailsSql);
			$marketDetails->prepare();
			$marketDetailsResult = $marketDetails->execute();
			
			if ($marketDetailsResult instanceof ResultInterface && $marketDetailsResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($marketDetailsResult);
			}
			return $resultSet;
		}
		
		public function getCatName() {
			$marketDetailsSql = "	
									SELECT 
										C.CATEGORY_ID, 
										rpad(' ',COUNT(C.CATEGORY_NAME)*5,'-')  AS CONTROLLER_DOT, 
										C.CATEGORY_NAME,
										C.LFT
								FROM 
										ls_category C, 
										ls_category P
								WHERE 
										C.LFT BETWEEN P.LFT AND P.RGT
								GROUP BY 
										C.CATEGORY_ID, C.CATEGORY_NAME, C.LFT 
								ORDER BY 
										C.LFT	
								";
			$marketDetails = $this->tableGateway->getAdapter()->createStatement($marketDetailsSql);
			$marketDetails->prepare();
			$marketDetailsResult = $marketDetails->execute();
			
			if ($marketDetailsResult instanceof ResultInterface && $marketDetailsResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($marketDetailsResult);
			}
			return $resultSet;
		}
		
		public function getCategoryPrice($id) {
			$id 		= (int) $id;
			$rowSet 	= $this->tableGateway->select(array('CAT_PRICE_ID' => $id));
			$row 		= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function categoryPriceExist($existCheckData) {
			$rowSet 	= $this->tableGateway->select($existCheckData);
			$row 		= $rowSet->current();
			return $row;
		}
		
		public function saveCategoryPrice(CategoryPrice $categoryPrice) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate1 		= date("Y-m-d", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$data = array(
				'CATEGORY_ID' 		=> $categoryPrice->CATEGORY_ID,
				'BUY_PRICE' 		=> str_replace(",","",$categoryPrice->BUY_PRICE),
				'SALE_PRICE' 		=> str_replace(",","",$categoryPrice->SALE_PRICE),
				'START_DATE' 		=> $businessDate1,
				'BUSINESS_DATE' 	=> $businessDate1,
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'CATEGORY_ID' 		=> $categoryPrice->CATEGORY_ID,
				'END_DATE IS NULL'
			);
			
			if($returnData = $this->categoryPriceExist($existCheckData)) {
				$instLotSizeId		 	= $returnData->CAT_PRICE_ID;
				$data1['END_DATE'] 		= $businessDate1;
				if($this->tableGateway->update($data1,array('CAT_PRICE_ID' => $instLotSizeId))){
					if($this->tableGateway->insert($data)) {
						return true;
					} else {
						return false;	
					}
				} else {
					return false;	
				}
			} else {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;	
				}
			}
		}
		
		public function deleteCategoryPrice($id) {
			if($this->tableGateway->delete(array('CAT_PRICE_ID' => $id))){
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