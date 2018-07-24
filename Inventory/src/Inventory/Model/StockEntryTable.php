<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class StockEntryTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		/*public function fetchAll() {
			$resultSet = $this->tableGateway->select(function(Select $select){
						 	$select->join('LS_INSTRUMENT_DETAILS','LS_INSTRUMENT_DETAILS.INSTRUMENT_DETAILS_ID=IS_DIVIDEND_DETAILS.INSTRUMENT_DETAILS_ID','INSTRUMENT_NAME');
							$select->where("IS_DIVIDEND_DETAILS.STATUS = 'np'");
							
						 });
			return $resultSet;
		}
		
		public function getDividend($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('DIVIDEND_DETAILS_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function dividendExist($existCheckData) {
			$rowSet 	= $this->tableGateway->select($existCheckData);
			$row 		= $rowSet->current();
			return $row;
		}*/
		
		public function saveStock($stockentry) {
			//return 1;die();
			//echo 'hi therere';die();
			//echo "<pre>"; print_r($stockentry); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$msg				= '';
			$flag				= 0;
			$qtys       	= array();
			$rates          = array();
			$tcAmounts      = array();
			$qty		  	= '';
			$rate		  	= '';
			$tcAmount		= '';
			$tcPortfolioCode	= $stockentry->BRANCH_ID;
			for($i = 0; $i < sizeof($stockentry->CATEGORY_ID); $i++) {
				$modelID	= $stockentry->CATEGORY_ID[$i];
				if(isset($stockentry->CATEGORY_ID[$i])) {
					$qty		= $stockentry->QUANTITY[$i];
					$rate		= $stockentry->BUY_PRICE[$i];
					$tcAmount 	= $stockentry->TOTAL_AMOUNT[$i];
					$data = array(
									'STOCK_ORDER_ID' => $stockentry->STOCK_ORDER_ID,
									'CATEGORY_ID' 	=> $stockentry->CATEGORY_ID[$i],
									'ORDER_NO' 		=> $stockentry->ORDER_NO,
									'CAT_PRICE_ID' 	=> $stockentry->CAT_PRICE_ID[$i],
									'SUPPLIER_INFO_ID' 	=> $stockentry->SUPPLIER_INFO_ID[$i],
									'QUANTITY' 		=> str_replace(",", "", $stockentry->QUANTITY[$i]),
									'BUY_PRICE' 	=> str_replace(",", "", $stockentry->BUY_PRICE[$i]),
									'TOTAL_AMOUNT' 	=> str_replace(",", "", $stockentry->TOTAL_AMOUNT[$i]),
									'AVG_RATE' 		=> str_replace(",", "", $stockentry->AVG_RATE[$i]),
									'DISCOUNT' 		=> str_replace(",", "", $stockentry->DISCOUNT[$i]),
									'NET_AMOUNT' 	=> str_replace(",", "", $stockentry->NET_AMOUNT[$i]),
									'STATUS' 		=> 'b',
									'SOLD_FLAG' 	=> '',
									'BUSINESS_DATE' => $businessDate,
									'RECORD_DATE' 	=> $recDate,
									'OPERATE_BY' 	=> $userId,
									'BRANCH_ID' 	=> $tcPortfolioCode,
									'INVOICE_NO'	=> $stockentry->INVOICE_NO,
									'REMARKS'		=> $stockentry->REMARKS[$i],
								);
					//echo "<pre>"; print_r($data); die();
					if(!empty($recDate) 
					   && !empty($qty) 
					   && !empty($rate) 
					   && !empty($tcAmount)) {
						$qtys[]			= $qty;
						$rats[]       	= $rate;
						$tcAmounts[]    = $tcAmount;
						$flag 			= 1;
						if($this->tableGateway->insert($data)) {
							//$this->tableGateway->adapter->getDriver()->getConnection()->commit();
							$transStatus = 1;
						} else {
							//$this->tableGateway->adapter->getDriver()->getConnection()->rollback();
							$transStatus = 0;
						}
					}
				}
			}
			if(!$transStatus) {
				return false;
			}
			else{
				return true;
			}
			
		}
		public function saveStockReturn($stockentry) {
			//return 1;die();
			//echo 'hi therere';die();
			//echo "<pre>"; print_r($stockentry); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$msg				= '';
			$flag				= 0;
			$qtys       	= array();
			$rates          = array();
			$tcAmounts      = array();
			$qty		  	= '';
			$rate		  	= '';
			$tcAmount		= '';
			$tcPortfolioCode	= $stockentry->BRANCH_ID;
			for($i = 0; $i < sizeof($stockentry->CATEGORY_ID); $i++) {
				$modelID	= $stockentry->CATEGORY_ID[$i];
				if(isset($stockentry->CATEGORY_ID[$i])) {
					$qty		= $stockentry->RQUANTITY[$i];
					$rate		= $stockentry->BUY_PRICE[$i];
					$tcAmount 	= $stockentry->TOTAL_AMOUNT[$i];
					$data = array(
									'STOCK_ORDER_ID' => $stockentry->STOCK_ORDER_ID,
									'CATEGORY_ID' 	=> $stockentry->CATEGORY_ID[$i],
									'ORDER_NO' 		=> $stockentry->ORDER_NO,
									'CAT_PRICE_ID' 	=> $stockentry->CAT_PRICE_ID[$i],
									'SUPPLIER_INFO_ID' 	=> $stockentry->SUPPLIER_INFO_ID[$i],
									'QUANTITY' 		=> str_replace(",", "", $stockentry->RQUANTITY[$i]),
									'BUY_PRICE' 	=> str_replace(",", "", $stockentry->BUY_PRICE[$i]),
									'TOTAL_AMOUNT' 	=> str_replace(",", "", $stockentry->TOTAL_AMOUNT[$i]),
									'AVG_RATE' 		=> str_replace(",", "", $stockentry->AVG_RATE[$i]),
									'REMARKS' 		=> $stockentry->REMARKS[$i],
									'DISCOUNT' 		=> str_replace(",", "", $stockentry->DISCOUNT[$i]),
									'NET_AMOUNT' 	=> str_replace(",", "", $stockentry->NET_AMOUNT[$i]),
									'STATUS' 		=> 'r',
									'SOLD_FLAG' 	=> '',
									'BUSINESS_DATE' => $businessDate,
									'RECORD_DATE' 	=> $recDate,
									'OPERATE_BY' 	=> $userId,
									'BRANCH_ID' 	=> $tcPortfolioCode
								);
					//echo "<pre>"; print_r($data); die();
					if(!empty($recDate) 
					   && !empty($qty) 
					   && !empty($rate) 
					   && !empty($tcAmount)) {
						$qtys[]			= $qty;
						$rats[]       	= $rate;
						$tcAmounts[]    = $tcAmount;
						$flag 			= 1;
						if($this->tableGateway->insert($data)) {
							//$this->tableGateway->adapter->getDriver()->getConnection()->commit();
							$compStockData = $this->fetchCompanyStockData($stockentry->CATEGORY_ID[$i]);
							$restOfQty = 0;
							$restOfQtyCheck = 0;
							$returnQuantity = $stockentry->RQUANTITY[$i];
							foreach ($compStockData as $selectOption) {
								$stockDetailsID			= $selectOption['STOCK_DETAILS_ID'];
								$compStockQty 			= $selectOption['QUANTITY'];
								$compStockCatPriceID 	= $selectOption['CAT_PRICE_ID'];
								$compStockSuppID 		= $selectOption['SUPPLIER_INFO_ID'];
								$compStockOderID 		= $selectOption['STOCK_ORDER_ID'];
								$compStockBuyPrice 		= $selectOption['BUY_PRICE'];
								$compStockAvgRate 		= $selectOption['AVG_RATE'];
								$compStockBranchID 		= $selectOption['BRANCH_ID'];
								
								if($compStockQty > $returnQuantity) {
									//echo 'elkane dhukce';die();
									$compStockTotalAmount	= $returnQuantity * $stockentry->BUY_PRICE[$i];
									$compStockNetAmount		= $compStockTotalAmount - $stockentry->DISCOUNT[$i];
									$compStockBuyQty		= $compStockQty - $returnQuantity;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $stockentry->DISCOUNT[$i];
									// buy entry section start
									$insertBuyQuery = "
														INSERT INTO 
																	i_stock_details
																		(	
																			STOCK_ORDER_ID,
																			CATEGORY_ID,
																			CAT_PRICE_ID,
																			SUPPLIER_INFO_ID,
																			QUANTITY,
																			BUY_PRICE,
																			TOTAL_AMOUNT,
																			AVG_RATE,
																			DISCOUNT,														
																			NET_AMOUNT,
																			STATUS,
																			SOLD_FLAG,
																			BUSINESS_DATE,
																			RECORD_DATE,
																			OPERATE_BY,
																			BRANCH_ID
																		)
																	VALUES
																		(
																			'".$compStockOderID."',
																			'".$stockentry->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $compStockBuyQty)."',
																			'".str_replace(",", "", $compStockBuyPrice)."',
																			'".$compStockBuyTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $stockentry->DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockBuyNetAmount)."',
																			'b',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			'".$compStockBranchID."'
																		)
															";
									$buyQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertBuyQuery);
									$buyQueryStatement->prepare();
									$buyQueryStatement->execute();
									// buy entry section end
									//previous buy row sold_flag update start
									$updateSoldFlagSql = "UPDATE i_stock_details SET	
																		SOLD_FLAG='block'
																		WHERE i_stock_details.STOCK_DETAILS_ID = '".$stockDetailsID."'
																";
									$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
									$soldFlagSqlStatement->prepare();
									$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									break;
								} else if ($compStockQty < $returnQuantity) {
									//echo 'ja sala elkane dhukce';die();
									$compStockTotalAmount		= $compStockQty * $stockentry->BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $stockentry->DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $stockentry->DISCOUNT[$i];
									if($restOfQtyCheck == 0){
										$restOfQty = $returnQuantity - $compStockQty;
										$returnQuantity = $restOfQty;
									} else {
										$restOfQty = $restOfQty - $compStockQty;
										$returnQuantity = $restOfQty;
									}
									// buy entry section start
									$insertBuyQuery = "
														INSERT INTO 
																	i_stock_details
																		(	
																			STOCK_ORDER_ID,
																			CATEGORY_ID,
																			CAT_PRICE_ID,
																			SUPPLIER_INFO_ID,
																			QUANTITY,
																			BUY_PRICE,
																			TOTAL_AMOUNT,
																			AVG_RATE,
																			DISCOUNT,														
																			NET_AMOUNT,
																			STATUS,
																			SOLD_FLAG,
																			BUSINESS_DATE,
																			RECORD_DATE,
																			OPERATE_BY,
																			BRANCH_ID
																		)
																	VALUES
																		(
																			'".$compStockOderID."',
																			'".$stockentry->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $compStockQty)."',
																			'".str_replace(",", "", $compStockBuyPrice)."',
																			'".$compStockBuyTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $stockentry->DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockBuyNetAmount)."',
																			'b',
																			'block',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			'".$compStockBranchID."'
																		)
															";
									$buyQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertBuyQuery);
									$buyQueryStatement->prepare();
									$buyQueryStatement->execute();
									// buy entry section end
									//previous buy row sold_flag update start
									$updateSoldFlagSql = "UPDATE i_stock_details SET	
																		SOLD_FLAG='block'
																		WHERE i_stock_details.STOCK_DETAILS_ID = '".$stockDetailsID."'
																";
									$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
									$soldFlagSqlStatement->prepare();
									$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									$restOfQtyCheck++;
								} else {
									$compStockTotalAmount		= $compStockQty * $stockentry->BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $stockentry->DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty - $returnQuantity;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $stockentry->DISCOUNT[$i];
									// sell entry section start
									$insertSaleQuery = "
														INSERT INTO 
																	i_stock_details
																		(	
																			STOCK_ORDER_ID,
																			CATEGORY_ID,
																			CAT_PRICE_ID,
																			SUPPLIER_INFO_ID,
																			QUANTITY,
																			SALE_PRICE,
																			TOTAL_AMOUNT,
																			AVG_RATE,
																			DISCOUNT,														
																			NET_AMOUNT,
																			STATUS,
																			SOLD_FLAG,
																			BUSINESS_DATE,
																			RECORD_DATE,
																			OPERATE_BY,
																			BRNACH_ID
																		)
																	VALUES
																		(
																			'".$compStockOderID."',
																			'".$stockentry->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $compStockBuyQty)."',
																			'".str_replace(",", "", $stockentry->BUY_PRICE[$i])."',
																			'".$compStockTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $stockentry->DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockNetAmount)."',
																			'b',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			'".$compStockBranchID."'
																		)
															";
									$saleQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertSaleQuery);
									$saleQueryStatement->prepare();
									$saleQueryStatement->execute();
									// sell entry section end
									//previous buy row sold_flag update start
									$updateSoldFlagSql = "UPDATE i_stock_details SET	
																		SOLD_FLAG='block'
																		WHERE i_stock_details.STOCK_DETAILS_ID = '".$stockDetailsID."'
																";
									$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
									$soldFlagSqlStatement->prepare();
									$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									break;
								}
							}
							$transStatus = 1;
						} else {
							//$this->tableGateway->adapter->getDriver()->getConnection()->rollback();
							$transStatus = 0;
						}
					}
				}
			}
			if(!$transStatus) {
				return false;
			}
			else{
				return true;
			}
			
		}
		public function updateStockEntry($stockentry) {
			
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$msg				= '';
			$flag				= 0;
			$qtys       	= array();
			$rates          = array();
			$tcAmounts      = array();
			$qty		  	= '';
			$rate		  	= '';
			$tcAmount		= '';
			$tcPortfolioCode	= $stockentry->BRANCH_ID;
			//echo 'hi tehrer';die();
			//echo "<pre>"; print_r($stockentry); die();
			for($i = 0; $i < sizeof($stockentry->CATEGORY_ID); $i++) {
				$stockDetailsId	= (int) $stockentry->STOCK_DETAILS_ID[$i];
				if(isset($stockentry->CATEGORY_ID[$i])) {
					$updateStockEntrySql = "UPDATE i_stock_details SET
														CATEGORY_ID='".$stockentry->CATEGORY_ID[$i]."',
														SUPPLIER_INFO_ID='".$stockentry->SUPPLIER_INFO_ID[$i]."',
														QUANTITY='".str_replace(",", "", $stockentry->QUANTITY[$i])."',
														RATE='".str_replace(",", "", $stockentry->RATE[$i])."',
														TOTAL_AMOUNT='".str_replace(",", "", $stockentry->TOTAL_AMOUNT[$i])."',
														AVG_RATE='".str_replace(",", "", $stockentry->AVG_RATE[$i])."',
														DISCOUNT='".str_replace(",", "", $stockentry->DISCOUNT[$i])."',
														NET_AMOUNT='".str_replace(",", "", $stockentry->NET_AMOUNT[$i])."',
														OPERATE_BY='".$userId."'
														WHERE STOCK_DETAILS_ID = '".$stockDetailsId."'
												";
					$updateStockEntryStatement = $this->tableGateway->getAdapter()->createStatement($updateStockEntrySql);
					$updateStockEntryStatement->prepare();
					if(!$updateStockEntryResult = $updateStockEntryStatement->execute()) {
						//return false;
						$msg = 0;
					} else {
						//return true;	
						$msg = 1;
					}
				}
			}
			return $msg;
		}
		
		public function getStockDetails($cond) {
			$select = "		
						SELECT SD.STOCK_DETAILS_ID,
							   SINFO.NAME, 
							   SD.CATEGORY_ID AS MODEL_ID, 
							   CAT.CATEGORY_NAME AS MODEL_NAME, 
							   SD.QUANTITY,
							   SD.RATE,
							   SD.TOTAL_AMOUNT,
							   SD.DISCOUNT,
							   SD.AVG_RATE,
							   SD.NET_AMOUNT,
							   SD.SUPPLIER_INFO_ID AS SUPPID
							FROM 							
								i_stock_details SD, i_stock_order SO, ls_supplier_info SINFO, ls_category CAT							
							WHERE SD.STOCK_ORDER_ID = SO.STOCK_ORDER_ID							
							AND SINFO.SUPPLIER_INFO_ID = SD.SUPPLIER_INFO_ID
							AND CAT.CATEGORY_ID = SD.CATEGORY_ID							
							{$cond}							
							ORDER BY SD.STOCK_DETAILS_ID ASC

			";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			$stockDataDetails = array();
			foreach($resultSet as $stockDatas) {
				$stockDataDetails['STOCK_DETAILS_ID'][] = $stockDatas["STOCK_DETAILS_ID"];
				$stockDataDetails['NAME'][] 			= $stockDatas["NAME"];
				$stockDataDetails['MODEL_ID'][] 		= $stockDatas["MODEL_ID"];
				$stockDataDetails['MODEL_NAME'][] 		= $stockDatas["MODEL_NAME"];
				$stockDataDetails['QUANTITY'][] 		= $stockDatas["QUANTITY"];
				$stockDataDetails['RATE'][] 			= $stockDatas["RATE"];
				$stockDataDetails['TOTAL_AMOUNT'][] 	= $stockDatas["TOTAL_AMOUNT"];
				$stockDataDetails['DISCOUNT'][] 		= $stockDatas["DISCOUNT"];
				$stockDataDetails['AVG_RATE'][] 		= $stockDatas["AVG_RATE"];
				$stockDataDetails['NET_AMOUNT'][] 		= $stockDatas["NET_AMOUNT"];
				$stockDataDetails['SUPPID'][] 			= $stockDatas["SUPPID"];
			}
			return $stockDataDetails;
			//return $resultSet;
		}
		public function fetchStockWiseModelDetails($input) {
			if(!empty($input)){
				$getTblDataSql   = "
									SELECT 
											DISTINCT
													i_stock_details.CATEGORY_ID,
													ls_category.CATEGORY_NAME,
													ls_category.CATEGORY_ID,
													ls_supp_wise_category.CATEGORY_ID,
													gs_coa.COA_CODE
											FROM 
													ls_category,
													i_stock_details,
													ls_supp_wise_category,
													gs_coa
													
											WHERE 
													LOWER( ls_category.CATEGORY_NAME ) = '".$input."'
													AND active_inactive = 'yes'
													AND gs_coa.COA_CODE = ls_category.COA_CODE
													AND ls_supp_wise_category.CATEGORY_ID = ls_category.CATEGORY_ID
													
													AND i_stock_details.CATEGORY_ID = ls_category.CATEGORY_ID
													AND (SELECT SUM( SD.QUANTITY )
													FROM i_stock_details SD
													WHERE SD.STATUS = 'b'
													AND SD.SOLD_FLAG = ''
													AND i_stock_details.CATEGORY_ID = SD.CATEGORY_ID
													) >0
											ORDER BY ls_category.CATEGORY_NAME ASC
											LIMIT 0 , 30
								   ";
			}else{
				$getTblDataSql   = "
									SELECT
											DISTINCT
													i_stock_details.CATEGORY_ID,
													ls_category.CATEGORY_NAME,
													ls_category.CATEGORY_ID,
													ls_supp_wise_category.CATEGORY_ID,
													gs_coa.COA_CODE
											FROM 
													ls_category,
													i_stock_details,
													ls_supp_wise_category,
													gs_coa
											WHERE  
													active_inactive = 'yes'
													AND i_stock_details.CATEGORY_ID = ls_category.CATEGORY_ID
													AND gs_coa.COA_CODE = ls_category.COA_CODE
													AND ls_supp_wise_category.CATEGORY_ID = ls_category.CATEGORY_ID
																									
													AND (SELECT SUM( SD.QUANTITY )
													FROM i_stock_details SD
													WHERE SD.STATUS = 'b'
													AND SD.SOLD_FLAG = ''
													AND i_stock_details.CATEGORY_ID = SD.CATEGORY_ID
													) >0
											ORDER BY ls_category.CATEGORY_NAME ASC
									
								   ";
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
		public function fetchSumofStockQuantity($catId) {
			$getTblDataSql   = "
								SELECT sum(SD.QUANTITY) AS QUANTITY,ls_cat_price.SALE_PRICE,ls_cat_price.CAT_PRICE_ID,ls_category.COA_CODE,gs_coa.COA_NAME,ls_cat_price.BUY_PRICE,ls_category.UNIT_CAL_IN
								FROM i_stock_details SD,ls_category, ls_cat_price, gs_coa
								WHERE SD.CATEGORY_ID =  '".$catId."'
								AND SD.STATUS = 'b'
								AND SD.SOLD_FLAG = ''
								AND ls_category.active_inactive = 'yes'
								AND SD.CATEGORY_ID = ls_category.CATEGORY_ID								
								AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
								AND gs_coa.COA_CODE = ls_category.COA_CODE
								AND ls_cat_price.END_DATE IS NULL
								";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchSumofStockQuantityForReturn($catId) {
			$getTblDataSql   = "
								SELECT sum(SD.QUANTITY) AS QUANTITY,ls_cat_price.BUY_PRICE,ls_cat_price.CAT_PRICE_ID,ls_category.COA_CODE,gs_coa.COA_NAME
								FROM i_stock_details SD,ls_category, ls_cat_price, gs_coa
								WHERE SD.CATEGORY_ID =  '".$catId."'
								AND SD.STATUS = 'b'
								AND SD.SOLD_FLAG = ''
								AND ls_category.active_inactive = 'yes'
								AND SD.CATEGORY_ID = ls_category.CATEGORY_ID								
								AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
								AND gs_coa.COA_CODE = ls_category.COA_CODE
								AND ls_cat_price.END_DATE IS NULL
								";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchCompanyStockData($catId) {
			$select = "		
						SELECT SD.STOCK_DETAILS_ID,
							   SD.QUANTITY AS QUANTITY,
							   SD.CAT_PRICE_ID,
							   SD.SUPPLIER_INFO_ID,
							   SD.STOCK_ORDER_ID,
							   SD.BUY_PRICE,
							   SD.AVG_RATE,
							   SD.BRANCH_ID
						FROM i_stock_details SD
						WHERE SD.CATEGORY_ID =  '".$catId."'
						AND SD.STATUS = 'b'
						AND SD.SOLD_FLAG = ''
						ORDER BY SD.CAT_PRICE_ID ASC
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
		public function fetchStockDetailsForInvoicePrint($stockOrderID,$status,$ORDERNO,$invoiceNo) {
			if($status == 'b'){
				$cond = " AND SD.STATUS = 'b' AND SD.INVOICE_NO != 'NULL' ";
			} else {
				$cond = " AND SD.STATUS = 's' ";
			}
			echo $select = "SELECT SD.STOCK_DETAILS_ID,
							   SD.QUANTITY AS QUANTITY,
							   SD.BUY_PRICE,
							   SD.AVG_RATE,
							   SD.DISCOUNT,
							   SD.TOTAL_AMOUNT,							   
							   SD.NET_AMOUNT,
							   ls_category.CATEGORY_NAME PRODUCT_NAME
						FROM i_stock_details SD, ls_category, ls_cat_price, i_stock_order SO
						WHERE SO.ORDER_NO  =  '".$ORDERNO."'
						AND SD.STOCK_ORDER_ID = SO.STOCK_ORDER_ID
						AND ls_category.active_inactive = 'yes'
						AND SD.CATEGORY_ID = ls_category.CATEGORY_ID								
						AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
						AND ls_cat_price.END_DATE IS NULL
						{$cond}
						ORDER BY SD.STOCK_DETAILS_ID ASC";die();
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			//return $resultSet;
			$purchaseDetails = array();
			foreach($resultSet as $purchaseDetails) {
				$purchaseDataDetails['STOCK_DETAILS_ID'][] = $purchaseDetails["STOCK_DETAILS_ID"];
				$purchaseDataDetails['QUANTITY'][] 		= $purchaseDetails["QUANTITY"];
				$purchaseDataDetails['BUY_PRICE'][] 	= $purchaseDetails["BUY_PRICE"];
				$purchaseDataDetails['AVG_RATE'][] 		= $purchaseDetails["AVG_RATE"];
				$purchaseDataDetails['DISCOUNT'][] 		= $purchaseDetails["DISCOUNT"];
				$purchaseDataDetails['TOTAL_AMOUNT'][] 	= $purchaseDetails["TOTAL_AMOUNT"];
				$purchaseDataDetails['NET_AMOUNT'][] 	= $purchaseDetails["NET_AMOUNT"];
				$purchaseDataDetails['PRODUCT_NAME'][] 	= $purchaseDetails["PRODUCT_NAME"];
			}
			return $purchaseDataDetails;
		}
		public function fetchStockDetailsForPurchaseEdit($stockOrderID,$status,$ORDERNO,$invoiceNo) {
			if($status == 'b'){
				$cond = " AND SD.STATUS = 'b' AND SD.INVOICE_NO != 'NULL' ";
			} else {
				$cond = " AND SD.STATUS = 's' ";
			}
			$select = "SELECT SD.STOCK_DETAILS_ID,
							   SD.QUANTITY AS QUANTITY,
							   SD.BUY_PRICE,
							   SD.AVG_RATE,
							   SD.DISCOUNT,
							   SD.TOTAL_AMOUNT,							   
							   SD.NET_AMOUNT,
							   ls_category.CATEGORY_NAME PRODUCT_NAME,
							   SD.CATEGORY_ID,
							   SD.CAT_PRICE_ID,
							   SD.AVG_RATE,
							   SO.ORDER_NO,
							   SO.NET_AMOUNT,
							   SO.PAYMENT_AMOUNT,
							   SO.REMAINING_AMOUNT
						FROM i_stock_details SD, ls_category, ls_cat_price, i_stock_order SO
						WHERE SO.ORDER_NO  =  '".$ORDERNO."'
						AND SD.STOCK_ORDER_ID = SO.STOCK_ORDER_ID
						AND ls_category.active_inactive = 'yes'
						AND SD.CATEGORY_ID = ls_category.CATEGORY_ID								
						AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
						AND ls_cat_price.END_DATE IS NULL
						{$cond}
						ORDER BY SD.STOCK_DETAILS_ID ASC";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			//return $resultSet;
			$purchaseDataDetails = array();
			foreach($resultSet as $purchaseDetails) {
				$purchaseDataDetails['STOCK_DETAILS_ID'][] = $purchaseDetails["STOCK_DETAILS_ID"];
				$purchaseDataDetails['QUANTITY'][] 		= $purchaseDetails["QUANTITY"];
				$purchaseDataDetails['BUY_PRICE'][] 	= $purchaseDetails["BUY_PRICE"];
				$purchaseDataDetails['AVG_RATE'][] 		= $purchaseDetails["AVG_RATE"];
				$purchaseDataDetails['DISCOUNT'][] 		= $purchaseDetails["DISCOUNT"];
				$purchaseDataDetails['TOTAL_AMOUNT'][] 	= $purchaseDetails["TOTAL_AMOUNT"];
				$purchaseDataDetails['NET_AMOUNT'][] 	= $purchaseDetails["NET_AMOUNT"];
				$purchaseDataDetails['PRODUCT_NAME'][] 	= $purchaseDetails["PRODUCT_NAME"];
				$purchaseDataDetails['CATEGORY_ID'][] 	= $purchaseDetails["CATEGORY_ID"];
				$purchaseDataDetails['CAT_PRICE_ID'][] 	= $purchaseDetails["CAT_PRICE_ID"];
				$purchaseDataDetails['AVG_RATE'][] 		= $purchaseDetails["AVG_RATE"];
				$purchaseDataDetails['ORDER_NO'][] 		= $purchaseDetails["ORDER_NO"];
				$purchaseDataDetails['NET_AMOUNT'][] 		= $purchaseDetails["NET_AMOUNT"];
				$purchaseDataDetails['PAYMENT_AMOUNT'][] 	= $purchaseDetails["PAYMENT_AMOUNT"];
				$purchaseDataDetails['REMAINING_AMOUNT'][] 	= $purchaseDetails["REMAINING_AMOUNT"];
				
			}
			return $purchaseDataDetails;
		}
		public function fetchSupplierWiseProductBuyDate($cond) {
			//echo $cond;
			echo $instrumentDetailsSql = "
									 SELECT DISTINCT CMSTOCK.BUSINESS_DATE
									  FROM i_stock_order CMOD, i_stock_details CMSTOCK
									 WHERE CMOD.CAP_MKT_ORDER_DETAILS_ID = CMSTOCK.CAP_MKT_ORDER_DETAILS_ID
									   AND CMSTOCK.STOCK_MATURE_DATE IS NULL
									   AND UPPER(CMSTOCK.STATUS) IN ('B')
									   AND CMSTOCK.INVOICE_NO != 'NULL'
									   {$cond}
									 ORDER BY CMSTOCK.BUSINESS_DATE ASC
									";die();
			$instrumentDetails = $this->tableGateway->getAdapter()->createStatement($instrumentDetailsSql);
			$instrumentDetails->prepare();
			$instrumentDetailsResult = $instrumentDetails->execute();
			
			if ($instrumentDetailsResult instanceof ResultInterface && $instrumentDetailsResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($instrumentDetailsResult);
			}
			return $resultSet;
		}
		
		public function fetchModelName($input,$suppInfoId) {
			if(!empty($input)){
				$getTblDataSql   = "SELECT  
											ls_supp_wise_category.CATEGORY_ID,
											ls_category.CATEGORY_NAME, 
											ls_cat_price.BUY_PRICE,
											ls_cat_price.CAT_PRICE_ID,
											ls_category.COA_CODE,
											gs_coa.COA_NAME
										FROM 
											ls_category,
											ls_cat_price,
											gs_coa,
											ls_supplier_info,
											ls_supp_wise_category
											
										WHERE 
											LOWER( ls_category.CATEGORY_NAME ) = '".strtolower($input)."'
											AND ls_category.active_inactive = 'yes'
											AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
											AND ls_cat_price.END_DATE IS NULL
											AND ls_supp_wise_category.END_DATE IS NULL
											AND gs_coa.COA_CODE = ls_category.COA_CODE
											AND ls_supp_wise_category.CATEGORY_ID = ls_category.CATEGORY_ID
											AND ls_supplier_info.SUPPLIER_INFO_ID = ls_supp_wise_category.SUPPLIER_INFO_ID
											AND ls_supp_wise_category.SUPPLIER_INFO_ID = '".$suppInfoId."'
											AND ls_supp_wise_category.IS_SUPPLY = 'yes'	
											
										ORDER BY ls_category.CATEGORY_NAME ASC
										
										LIMIT 0 , 10";
			}else{
			$getTblDataSql   = "SELECT  
										ls_supp_wise_category.CATEGORY_ID,
										ls_category.CATEGORY_NAME, 
										ls_cat_price.BUY_PRICE,
										ls_cat_price.CAT_PRICE_ID,
										ls_category.COA_CODE,
										gs_coa.COA_NAME
									FROM 
										ls_category,
										ls_cat_price,
										gs_coa,
										ls_supplier_info,
										ls_supp_wise_category
									WHERE 
										ls_category.active_inactive = 'yes'
										AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
										AND ls_cat_price.END_DATE IS NULL
										AND ls_supp_wise_category.END_DATE IS NULL
										AND gs_coa.COA_CODE = ls_category.COA_CODE
										AND ls_supp_wise_category.CATEGORY_ID = ls_category.CATEGORY_ID
										AND ls_supplier_info.SUPPLIER_INFO_ID = ls_supp_wise_category.SUPPLIER_INFO_ID
										AND ls_supp_wise_category.SUPPLIER_INFO_ID = '".$suppInfoId."'
										AND ls_supp_wise_category.IS_SUPPLY = 'yes'
									ORDER BY ls_category.CATEGORY_NAME ASC
										";
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