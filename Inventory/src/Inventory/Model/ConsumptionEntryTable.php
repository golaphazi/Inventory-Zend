<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class ConsumptionEntryTable {
		protected $tableGateway;		
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function stockOrderExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		public function saveConsumption($consumptionentry) {
			//return 1;die();
			//echo 'hi therere';die();
			//echo "<pre>"; print_r($consumptionentry); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$msg				= '';
			$branchID			= $consumptionentry->BRANCH_ID;
			
			// Inserting into stock order table start //
			$existCheckData = array(
										'STOCK_ORDER_ID' => '',
									);
			$stockOrderID = 0;
			$insertSaleQuery = "
								INSERT INTO 
											i_stock_order
												(	
													ORDER_NO,
													TOTAL_AMOUNT,
													DISCOUNT_AMOUNT,
													NET_AMOUNT,
													PAYMENT_AMOUNT,
													REMAINING_AMOUNT,
													DISCOUNT_TYPE,
													ORDER_TYPE,
													BUSINESS_DATE,
													RECORD_DATE,
													OPERATE_BY
												)
											VALUES
												(
													".$this->getMaxStockOrderNo().",
													'".$consumptionentry->NET_PAYMENT."',
													'0.00',
													'".$consumptionentry->NET_PAYMENT."',
													'0.00',
													'0.00',
													'',
													'consumption',
													'".$businessDate."',
													'".$recDate."',
													'".$userId."'
												)
									";
			$saleQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertSaleQuery);
			$saleQueryStatement->prepare();				
			if($saleQueryStatement->execute()) {
				$selectMaxID = "SELECT MAX(STOCK_ORDER_ID) AS STOCK_ORDER_ID FROM i_stock_order";
				$stmt = $this->tableGateway->getAdapter()->createStatement($selectMaxID);
				$stmt->prepare();
				$result = $stmt->execute();
				if ($result instanceof ResultInterface && $result->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($result);
				}
				foreach($resultSet as $stockDatas) {
					$stockOrderID = $stockDatas["STOCK_ORDER_ID"];
				}
				for($i = 0; $i <= sizeof($consumptionentry->CATEGORY_ID); $i++) {
					if(isset($consumptionentry->CATEGORY_ID[$i])) {
						$data = array(
										'CATEGORY_ID' 	=> $consumptionentry->CATEGORY_ID[$i],
										'BRANCH_ID' 	=> $consumptionentry->BRANCH_ID,
										'CONSUMPTION_NO' => $consumptionentry->CONSUMPTION_NO,
										'QUANTITY' 		=> str_replace(",", "", $consumptionentry->QUANTITY[$i]),
										'RATE' 			=> str_replace(",", "", $consumptionentry->RATE[$i]),
										'TOTAL_AMOUNT' 	=> str_replace(",", "", $consumptionentry->TOTAL_AMOUNT[$i]),
										'CONSUMPTION_STATUS' => 'stockuse',
										'BUSINESS_DATE' => $businessDate,
										'RECORD_DATE' 	=> $recDate,
										'OPERATE_BY' 	=> $userId,
									);
						//echo "<pre>"; print_r($data); die();
						if($this->tableGateway->insert($data)) {						
							// Stock reduce start //
							$compStockData = $this->fetchCompanyStockData($consumptionentry->CATEGORY_ID[$i]);
							$restOfQty = 0;
							$restOfQtyCheck = 0;
							$retQuantity = $consumptionentry->QUANTITY[$i];
							foreach ($compStockData as $selectOption) {
								$stockDetailsID			= $selectOption['STOCK_DETAILS_ID'];
								$compStockQty 			= $selectOption['QUANTITY'];
								$compStockCatPriceID 	= $selectOption['CAT_PRICE_ID'];
								$compStockSuppID 		= $selectOption['SUPPLIER_INFO_ID'];
								$compStockOderID 		= $selectOption['STOCK_ORDER_ID'];
								$compStockBuyPrice 		= $selectOption['BUY_PRICE'];
								$compStockAvgRate 		= $selectOption['AVG_RATE'];
								
								if($compStockQty > $retQuantity) {
									$compStockTotalAmount	= $retQuantity * $consumptionentry->RATE[$i];
									$compStockNetAmount		= $compStockTotalAmount;
									$compStockBuyQty		= $compStockQty - $retQuantity;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount;
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
																				ORDER_NO
																			)
																		VALUES
																			(
																				'".$stockOrderID."',
																				'".$consumptionentry->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $retQuantity)."',
																				'".str_replace(",", "", $consumptionentry->RATE[$i])."',
																				'".$compStockTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'0.00',
																				'".str_replace(",", "", $compStockNetAmount)."',
																				's',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				''
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
																				OPERATE_BY,
																				ORDER_NO
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$consumptionentry->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockBuyQty)."',
																				'".str_replace(",", "", $compStockBuyPrice)."',
																				'".$compStockBuyTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'0.00',
																				'".str_replace(",", "", $compStockBuyNetAmount)."',
																				'b',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				''
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
								} else if ($compStockQty < $retQuantity) {
									$compStockTotalAmount		= $compStockQty * $consumptionentry->RATE[$i];
									$compStockNetAmount			= $compStockTotalAmount;
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount;
										if($restOfQtyCheck == 0){
											$restOfQty = $retQuantity - $compStockQty;
											$retQuantity = $restOfQty;
										} else {
											$restOfQty = $restOfQty - $compStockQty;
											$retQuantity = $restOfQty;
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
																				OPERATE_BY,
																				ORDER_NO
																			)
																		VALUES
																			(
																				'".$stockOrderID."',
																				'".$consumptionentry->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockQty)."',
																				'".str_replace(",", "", $consumptionentry->RATE[$i])."',
																				'".$compStockTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'0.00',
																				'".str_replace(",", "", $compStockNetAmount)."',
																				's',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				''
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
																				OPERATE_BY,
																				ORDER_NO
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$consumptionentry->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockQty)."',
																				'".str_replace(",", "", $compStockBuyPrice)."',
																				'".$compStockBuyTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'0.00',
																				'".str_replace(",", "", $compStockBuyNetAmount)."',
																				'b',
																				'block',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				''
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
									$compStockTotalAmount		= $compStockQty * $consumptionentry->RATE[$i];
									$compStockNetAmount			= $compStockTotalAmount;
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount;
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
																			ORDER_NO
																		)
																	VALUES
																		(
																			'".$stockOrderID."',
																			'".$consumptionentry->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $retQuantity)."',
																			'".str_replace(",", "", $consumptionentry->RATE[$i])."',
																			'".$compStockTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'0.00',
																			'".str_replace(",", "", $compStockNetAmount)."',
																			's',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			''
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
							//Stcok reduce end //
							$transStatus = 1;
						} else {
							$transStatus = 0;
						}
					}
				}
				if($consumptionentry->isProduction == 'yes'){
					$productionStockOrderID = 0;
					$insertSaleQuery = "
										INSERT INTO 
													i_stock_order
														(	
															ORDER_NO,
															TOTAL_AMOUNT,
															DISCOUNT_AMOUNT,
															NET_AMOUNT,
															PAYMENT_AMOUNT,
															REMAINING_AMOUNT,
															DISCOUNT_TYPE,
															ORDER_TYPE,
															BUSINESS_DATE,
															RECORD_DATE,
															OPERATE_BY
														)
													VALUES
														(
															".$this->getMaxStockOrderNo().",
															'".$consumptionentry->P_NET_PAYMENT."',
															'0.00',
															'".$consumptionentry->P_NET_PAYMENT."',
															'0.00',
															'0.00',
															'',
															'production',
															'".$businessDate."',
															'".$recDate."',
															'".$userId."'
														)
											";
					$saleQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertSaleQuery);
					$saleQueryStatement->prepare();				
					if($saleQueryStatement->execute()) {
						$selectMaxID = "SELECT MAX(STOCK_ORDER_ID) AS STOCK_ORDER_ID FROM i_stock_order";
						$stmt = $this->tableGateway->getAdapter()->createStatement($selectMaxID);
						$stmt->prepare();
						$result = $stmt->execute();
						if ($result instanceof ResultInterface && $result->isQueryResult()) {
							$resultSet = new ResultSet();
							$resultSet->initialize($result);
						}
						foreach($resultSet as $stockDatas) {
							$productionStockOrderID = $stockDatas["STOCK_ORDER_ID"];
						}
						// consumption table insert start//
						$supplierInfoID = '';
						for($j = 0; $j < sizeof($consumptionentry->P_CATEGORY_ID); $j++) {
							if(isset($consumptionentry->P_CATEGORY_ID[$j])) {
								$supplierInfoID = $this->getCatWiseSupplierInfo($consumptionentry->P_CATEGORY_ID[$j]);
								$productedData = array(
														'CATEGORY_ID' 	=> $consumptionentry->P_CATEGORY_ID[$j],
														'BRANCH_ID' 	=> $consumptionentry->BRANCH_ID,
														'CONSUMPTION_NO' => $consumptionentry->CONSUMPTION_NO,
														'QUANTITY' 		=> str_replace(",", "", $consumptionentry->P_QUANTITY[$j]),
														'RATE' 			=> str_replace(",", "", $consumptionentry->P_RATE[$j]),
														'TOTAL_AMOUNT' 	=> str_replace(",", "", $consumptionentry->P_TOTAL_AMOUNT[$j]),
														'CONSUMPTION_STATUS' => 'production',
														'BUSINESS_DATE' => $businessDate,
														'RECORD_DATE' 	=> $recDate,
														'OPERATE_BY' 	=> $userId,
													);
								//echo "<pre>"; print_r($productedData); die();
								if($this->tableGateway->insert($productedData)) {
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
																			ORDER_NO
																		)
																	VALUES
																		(
																			'".$productionStockOrderID."',
																			'".$consumptionentry->P_CATEGORY_ID[$j]."',
																			'".$consumptionentry->P_CAT_PRICE_ID[$j]."',
																			'".$supplierInfoID."',
																			'".$consumptionentry->P_QUANTITY[$j]."',
																			'".$consumptionentry->P_RATE[$j]."',
																			'".$consumptionentry->P_TOTAL_AMOUNT[$j]."',
																			'".$consumptionentry->P_RATE[$j]."',
																			'0.00',
																			'".$consumptionentry->P_TOTAL_AMOUNT[$j]."',
																			'b',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			''
																		)
															";
									$buyQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertBuyQuery);
									$buyQueryStatement->prepare();
									$buyQueryStatement->execute();
									$transStatus = 1;
								} else {
									$transStatus = 0;
								}
							}
						}
						// consumption table insert end//
					}
				}
			}
			// Inserting into stock order table end //
			
			
			if(!$transStatus) {
				return false;
			}
			else {
				return $stockOrderID;
			}
			
			//return 1;
			/*$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$data = array(
							'ORDER_NO' 				=> $retstockorder->ORDER_NO,
							'EMPLOYEE_ID' 			=> $retstockorder->EMPLOYEE_ID,
							'RETAILER_ID' 			=> $retstockorder->RETAILER_ID,
							'RET_TOTAL_AMOUNT' 		=> str_replace(",", "", $retstockorder->RET_TOTAL_AMOUNT),
							'RET_DISCOUNT_AMOUNT' 	=> str_replace(",", "", $retstockorder->RET_DISCOUNT_AMOUNT),
							'ORDER_TYPE' 			=> 'sale',
							//'DISCOUNT_TYPE' 	=> str_replace(",", "", $stockorder->DISCOUNT_TYPE),
							'BUSINESS_DATE' => $businessDate,
							'RECORD_DATE' 	=> $recDate,
							'OPERATE_BY' 	=> $userId,
						);
			$existCheckData = array(
				'ORDER_NO' => $retstockorder->ORDER_NO,
			);
			if($this->tableGateway->insert($data)) {
				$insertSaleQuery = "
									INSERT INTO 
												i_stock_order
													(	
														ORDER_NO,
														TOTAL_AMOUNT,
														DISCOUNT_AMOUNT,
														ORDER_TYPE,
														BUSINESS_DATE,
														RECORD_DATE,
														OPERATE_BY
													)
												VALUES
													(
														'',
														'".$retstockorder->RET_TOTAL_AMOUNT."',
														'".$retstockorder->RET_DISCOUNT_AMOUNT."',
														'sale',
														'".$businessDate."',
														'".$recDate."',
														'".$userId."'
													)
										";
				$saleQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertSaleQuery);
				$saleQueryStatement->prepare();
				$saleQueryStatement->execute();
				$stockorderSql = $this->stockOrderExist($existCheckData);
				$stockOrderID = (int) $stockorderSql->RET_STOCK_DIST_ID;
				return $stockOrderID;
				//$transStatus = 1;
			} else {
				$transStatus = 0;
			}*/
		}
		public function saveRETStockReturn($retstockorder) {
			//return 1;
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$data = array(
							'ORDER_NO' 				=> $retstockorder->ORDER_NO,
							'EMPLOYEE_ID' 			=> $retstockorder->EMPLOYEE_ID,
							'RETAILER_ID' 			=> $retstockorder->RETAILER_ID,
							'RET_TOTAL_AMOUNT' 		=> str_replace(",", "", $retstockorder->RET_TOTAL_AMOUNT),
							'RET_DISCOUNT_AMOUNT' 	=> str_replace(",", "", $retstockorder->RET_DISCOUNT_AMOUNT),
							'ORDER_TYPE' 	=> 'return',
							'BUSINESS_DATE' => $businessDate,
							'RECORD_DATE' 	=> $recDate,
							'OPERATE_BY' 	=> $userId,
						);
			$existCheckData = array(
				'ORDER_NO' => $retstockorder->ORDER_NO,
			);
			if($this->tableGateway->insert($data)) {
				/*$insertSaleQuery = "
									INSERT INTO 
												i_stock_order
													(	
														ORDER_NO,
														TOTAL_AMOUNT,
														DISCOUNT_AMOUNT,
														ORDER_TYPE,
														BUSINESS_DATE,
														RECORD_DATE,
														OPERATE_BY
													)
												VALUES
													(
														'',
														'".$retstockorder->RET_TOTAL_AMOUNT."',
														'".$retstockorder->RET_DISCOUNT_AMOUNT."',
														'sale',
														'".$businessDate."',
														'".$recDate."',
														'".$userId."'
													)
										";
				$saleQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertSaleQuery);
				$saleQueryStatement->prepare();
				$saleQueryStatement->execute();
				*/
				$stockorderSql = $this->stockOrderExist($existCheckData);
				$stockOrderID = (int) $stockorderSql->RET_STOCK_DIST_ID;
				return $stockOrderID;
				//$transStatus = 1;
			} else {
				$transStatus = 0;
			}
			//return $transStatus;
		}
		public function updateRETStockOrder($stockorder) {
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
		public function getRETStockOrder($cond) {
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
		public function getMaxStockOrderNo() {
			$defaultOrderNo = 1;
			$maxOrderNo = '';
			$maxOrderSql = "SELECT MAX(ORDER_NO) AS MAXORDER FROM i_stock_order";
			$stmt = $this->tableGateway->getAdapter()->createStatement($maxOrderSql);
			$stmt->prepare();
			$result = $stmt->execute();
			foreach ($result as $maxOrderNo) {
				$defaultOrderNo = $maxOrderNo['MAXORDER']+1;
			}
			return $defaultOrderNo;
		}
		public function getRetailerOrderInfoForInvoice($retOrderID) {
			$select = "		
						SELECT RS.RET_STOCK_DIST_ID, RS.ORDER_NO, RS.RET_TOTAL_AMOUNT, RS.RET_DISCOUNT_AMOUNT, RS.BUSINESS_DATE, RS.RETAILER_ID
						FROM i_retailer_stock_dist RS
						WHERE RS.RET_STOCK_DIST_ID = '".$retOrderID."'
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
		public function fetchConsumptionNo($input) {
			$getTblDataSql   = "SELECT DISTINCT CONSUMPTION_NO FROM i_consumption where CONSUMPTION_NO like '%".$input."%' order by CONSUMPTION_ID asc limit 10";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function getMaxStockDetailsID() {			
			$maxSDID = '';
			$maxOrderSql = "SELECT MAX(STOCK_ORDER_ID) AS MAXSDID FROM i_stock_order";
			$stmt = $this->tableGateway->getAdapter()->createStatement($maxOrderSql);
			$stmt->prepare();
			$result = $stmt->execute();
			foreach ($result as $maxSD) {
				$maxSDID = $maxSD['MAXSDID'];
			}
			return $maxSDID;
		}
		public function getCatWiseSupplierInfo($id) {
			$supplierInfoID = '';
			$select = "SELECT suppcat.SUPPLIER_INFO_ID AS SUPPLIER_INFO_ID
						FROM ls_supp_wise_category suppcat
						WHERE suppcat.CATEGORY_ID = '".$id."'
						AND suppcat.IS_SUPPLY = 'yes'
						AND suppcat.END_DATE is NULL
						";
			$selectStatement = $this->tableGateway->getAdapter()->createStatement($select);
			$selectStatement->prepare();
			$selectStatementResult 	= $selectStatement->execute();
			if ($selectStatementResult instanceof ResultInterface && $selectStatementResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectStatementResult);
			}
			foreach ($resultSet as $maxSD) {
				$supplierInfoID = $maxSD['SUPPLIER_INFO_ID'];
			}
			return $supplierInfoID;
		}
		public function fetchConsumptionForReport($cond) {
			$getTblDataSql   = "SELECT consumption.CONSUMPTION_ID AS CONSUMPTION_ID,
											consumption.CATEGORY_ID,
										consumption.QUANTITY,
										consumption.BRANCH_ID,
										c_branch.BRANCH_NAME,
										consumption.CONSUMPTION_NO,
										consumption.RATE,
										consumption.TOTAL_AMOUNT,
										consumption.CONSUMPTION_STATUS,
										ls_category.CATEGORY_NAME,
										ls_category.P_CODE
								FROM 
										i_consumption consumption,c_branch, ls_category 
								WHERE
										consumption.CATEGORY_ID = ls_category.CATEGORY_ID
								AND		c_branch.BRANCH_ID = consumption.BRANCH_ID
								{$cond}
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