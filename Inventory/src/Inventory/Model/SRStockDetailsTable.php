<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class SRStockDetailsTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function stockOrderExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		public function saveSRStockDetails($srstockdetails) {
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
			for($i = 0; $i < sizeof($srstockdetails->CATEGORY_ID); $i++) {
				$modelID	= $srstockdetails->CATEGORY_ID[$i];
				if(isset($srstockdetails->CATEGORY_ID[$i])) {
					$qty		= $srstockdetails->SR_QUANTITY[$i];
					$rate		= $srstockdetails->SR_BUY_PRICE[$i];
					$tcAmount 	= $srstockdetails->SR_TOTAL_AMOUNT[$i];
					$data = array(
									'SR_STOCK_DIST_ID' 	=> $srstockdetails->SR_STOCK_DIST_ID,
									'CATEGORY_ID' 		=> $srstockdetails->CATEGORY_ID[$i],
									'CAT_PRICE_ID' 		=> $srstockdetails->CAT_PRICE_ID[$i],
									'SR_QUANTITY' 		=> str_replace(",", "", $srstockdetails->SR_QUANTITY[$i]),
									'SR_BUY_PRICE' 		=> str_replace(",", "", $srstockdetails->SR_BUY_PRICE[$i]),
									'SR_TOTAL_AMOUNT' 	=> str_replace(",", "", $srstockdetails->SR_TOTAL_AMOUNT[$i]),
									'SR_AVG_RATE' 		=> str_replace(",", "", $srstockdetails->SR_AVG_RATE[$i]),
									'SR_DISCOUNT' 		=> str_replace(",", "", $srstockdetails->SR_DISCOUNT[$i]),
									'SR_NET_AMOUNT' 	=> str_replace(",", "", $srstockdetails->SR_NET_AMOUNT[$i]),
									'STATUS' 		=> 'b',
									'SOLD_FLAG'	=> '',
									'BUSINESS_DATE' => $businessDate,
									'RECORD_DATE' 	=> $recDate,
									'OPERATE_BY' 	=> $userId,
								);
					//echo '<pre>';print_r($data);die();
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
							$compStockData = $this->fetchCompanyStockData($srstockdetails->CATEGORY_ID[$i]);
							$restOfQty = 0;
							$restOfQtyCheck = 0;
							$srQuantity = $srstockdetails->SR_QUANTITY[$i];
							foreach ($compStockData as $selectOption) {
								$stockDetailsID			= $selectOption['STOCK_DETAILS_ID'];
								$compStockQty 			= $selectOption['QUANTITY'];
								$compStockCatPriceID 	= $selectOption['CAT_PRICE_ID'];
								$compStockSuppID 		= $selectOption['SUPPLIER_INFO_ID'];
								$compStockOderID 		= $selectOption['STOCK_ORDER_ID'];
								$compStockBuyPrice 		= $selectOption['BUY_PRICE'];
								$compStockAvgRate 		= $selectOption['AVG_RATE'];
								
								if($compStockQty > $srQuantity) {
									$compStockTotalAmount	= $srQuantity * $srstockdetails->SR_BUY_PRICE[$i];
									$compStockNetAmount		= $compStockTotalAmount - $srstockdetails->SR_DISCOUNT[$i];
									$compStockBuyQty		= $compStockQty - $srQuantity;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $srstockdetails->SR_DISCOUNT[$i];
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
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$srstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $srQuantity)."',
																				'".str_replace(",", "", $srstockdetails->SR_BUY_PRICE[$i])."',
																				'".$compStockTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $srstockdetails->SR_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockNetAmount)."',
																				's',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."'
																			)
																";
										$saleQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertSaleQuery);
										$saleQueryStatement->prepare();
										$saleQueryStatement->execute();
									// sell entry section end
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
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$srstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockBuyQty)."',
																				'".str_replace(",", "", $compStockBuyPrice)."',
																				'".$compStockBuyTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $srstockdetails->SR_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockBuyNetAmount)."',
																				'b',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."'
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
								} else if ($compStockQty < $srQuantity) {
									
									$compStockTotalAmount		= $compStockQty * $srstockdetails->SR_BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $srstockdetails->SR_DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $srstockdetails->SR_DISCOUNT[$i];
									
									
										if($restOfQtyCheck == 0){
											$restOfQty = $srQuantity - $compStockQty;
											$srQuantity = $restOfQty;
										} else {
											$restOfQty = $restOfQty - $compStockQty;
											$srQuantity = $restOfQty;
										}
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
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$srstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockQty)."',
																				'".str_replace(",", "", $srstockdetails->SR_BUY_PRICE[$i])."',
																				'".$compStockTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $srstockdetails->SR_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockNetAmount)."',
																				's',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."'
																			)
																";
										$saleQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertSaleQuery);
										$saleQueryStatement->prepare();
										$saleQueryStatement->execute();
									// sell entry section end
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
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$srstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockQty)."',
																				'".str_replace(",", "", $compStockBuyPrice)."',
																				'".$compStockBuyTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $srstockdetails->SR_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockBuyNetAmount)."',
																				'b',
																				'block',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."'
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
									$compStockTotalAmount		= $compStockQty * $srstockdetails->SR_BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $srstockdetails->SR_DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $srstockdetails->SR_DISCOUNT[$i];
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
																			OPERATE_BY
																		)
																	VALUES
																		(
																			'".$compStockOderID."',
																			'".$srstockdetails->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $srQuantity)."',
																			'".str_replace(",", "", $srstockdetails->SR_BUY_PRICE[$i])."',
																			'".$compStockTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $srstockdetails->SR_DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockNetAmount)."',
																			's',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."'
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
		public function updateSRStockOrder($stockorder) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$id = (int) $stockorder->STOCK_ORDER_ID;
			$cond = $stockorder->COND;
			$updateStockOrderSql = '';
			if($cond == 'y'){
				$updateStockOrderSql = "UPDATE i_stock_order SET	
													TOTAL_AMOUNT = TOTAL_AMOUNT + '".str_replace(",", "", $stockorder->TOTAL_AMOUNT)."',
													DISCOUNT_AMOUNT = DISCOUNT_AMOUNT + '".str_replace(",", "", $stockorder->DISCOUNT_AMOUNT)."',
													NET_AMOUNT = NET_AMOUNT + '".str_replace(",", "", $stockorder->NET_AMOUNT)."',
													PAYMENT_AMOUNT = PAYMENT_AMOUNT + '".str_replace(",", "", $stockorder->PAYMENT_AMOUNT)."',
													REMAINING_AMOUNT= REMAINING_AMOUNT + '".str_replace(",", "", $stockorder->REMAINING_AMOUNT)."'
													WHERE STOCK_ORDER_ID = '".$id."'
											";
			} else {
				$updateStockOrderSql = "UPDATE i_stock_order SET	
													TOTAL_AMOUNT='".str_replace(",", "", $stockorder->TOTAL_AMOUNT)."',
													DISCOUNT_AMOUNT='".str_replace(",", "", $stockorder->DISCOUNT_AMOUNT)."',
													NET_AMOUNT='".str_replace(",", "", $stockorder->NET_AMOUNT)."',
													PAYMENT_AMOUNT='".str_replace(",", "", $stockorder->PAYMENT_AMOUNT)."',
													REMAINING_AMOUNT='".str_replace(",", "", $stockorder->REMAINING_AMOUNT)."'
													WHERE STOCK_ORDER_ID = '".$id."'
											";
			}
			$updateStockOrderStatement = $this->tableGateway->getAdapter()->createStatement($updateStockOrderSql);
			$updateStockOrderStatement->prepare();
			if(!$updateStockOrderResult = $updateStockOrderStatement->execute()) {
				return false;
			} else {
				return true;	
			}		
			
			
		}
		public function getSRStockOrder($cond) {
			$select = "		
						SELECT 
								SO.STOCK_ORDER_ID,SO.ORDER_NO,TOTAL_AMOUNT,DISCOUNT_AMOUNT,NET_AMOUNT,PAYMENT_AMOUNT,REMAINING_AMOUNT
						FROM 
								i_stock_order SO
						{$cond}
						ORDER BY 
								SO.ORDER_NO ASC
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
		public function fetchCatFromSRStock($input) {
			$getTblDataSql   = "
								SELECT DISTINCT i_sr_stock_dist_details.CATEGORY_ID, ls_category.CATEGORY_NAME
								FROM ls_category, i_sr_stock_dist_details
								WHERE LOWER( ls_category.CATEGORY_NAME ) LIKE '".$input."%'
								AND ls_category.active_inactive = 'yes'
								AND i_sr_stock_dist_details.CATEGORY_ID = ls_category.CATEGORY_ID
								AND (SELECT SUM( SRSD.SR_QUANTITY )
								FROM i_sr_stock_dist_details SRSD
								WHERE SRSD.STATUS = 'b'
								AND SRSD.SOLD_FLAG = ''
								AND i_sr_stock_dist_details.CATEGORY_ID = SRSD.CATEGORY_ID
								) >0
								ORDER BY ls_category.CATEGORY_NAME ASC
							   ";
										/*SELECT DISTINCT i_stock_details.CATEGORY_ID, ls_category.CATEGORY_NAME, ls_cat_price.SALE_PRICE,ls_cat_price.CAT_PRICE_ID
										FROM ls_category, ls_cat_price, i_stock_details
										WHERE LOWER( ls_category.CATEGORY_NAME ) LIKE '%".$input."%'
										AND active_inactive = 'yes'
										AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
										AND ls_cat_price.END_DATE IS NULL 
										ORDER BY ls_category.CATEGORY_NAME ASC
										LIMIT 0 , 10*/
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
							   SD.AVG_RATE
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
		public function fetchSumofStockQuantityFromSR($catId) {
			$getTblDataSql   = "SELECT sum(SRSD.SR_QUANTITY) AS SRQUANTITY,ls_cat_price.SALE_PRICE,ls_cat_price.CAT_PRICE_ID,ls_category.COA_CODE,gs_coa.COA_NAME
								FROM i_sr_stock_dist_details SRSD,ls_category, ls_cat_price, gs_coa
								WHERE SRSD.CATEGORY_ID =  '".$catId."'
								AND SRSD.STATUS = 'b'
								AND SRSD.SOLD_FLAG = ''
								AND ls_category.active_inactive = 'yes'
								AND SRSD.CATEGORY_ID = ls_category.CATEGORY_ID								
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
		
	}
?>	