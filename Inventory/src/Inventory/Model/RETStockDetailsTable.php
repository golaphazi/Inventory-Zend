<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class RETStockDetailsTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function stockOrderExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		public function saveRETStockDetails($retstockdetails) {
			//return 1;
			//echo '<pre>';print_r($retstockdetails);die();
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
			$tcPortfolioCode	= $retstockdetails->BRANCH_ID;
			for($i = 0; $i < sizeof($retstockdetails->CATEGORY_ID); $i++) {
				$modelID	= $retstockdetails->CATEGORY_ID[$i];
				if(isset($retstockdetails->CATEGORY_ID[$i])) {
					$qty		= $retstockdetails->RET_QUANTITY[$i];
					$rate		= $retstockdetails->RET_BUY_PRICE[$i];
					$tcAmount 	= $retstockdetails->RET_TOTAL_AMOUNT[$i];
					$data = array(
									'RET_STOCK_DIST_ID' => $retstockdetails->RET_STOCK_DIST_ID,
									'CATEGORY_ID' 		=> $retstockdetails->CATEGORY_ID[$i],
									'CAT_PRICE_ID' 		=> $retstockdetails->CAT_PRICE_ID[$i],
									'RET_QUANTITY' 		=> str_replace(",", "", $retstockdetails->RET_QUANTITY[$i]),
									'RET_BUY_PRICE' 	=> str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i]),
									'RET_TOTAL_AMOUNT' 	=> str_replace(",", "", $retstockdetails->RET_TOTAL_AMOUNT[$i]),
									'RET_AVG_RATE' 		=> str_replace(",", "", $retstockdetails->RET_AVG_RATE[$i]),
									'RET_DISCOUNT' 		=> str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i]),
									'RET_DISCOUNT_RECEIVE' => str_replace(",", "", $retstockdetails->RET_DISCOUNT_RECEIVE[$i]),
									'RET_NET_AMOUNT' 	=> str_replace(",", "", $retstockdetails->RET_NET_AMOUNT[$i]),
									'STATUS' 		=> 'b',
									'SOLD_FLAG'	=> '',
									'BUSINESS_DATE' => $businessDate,
									'RECORD_DATE' 	=> $recDate,
									'OPERATE_BY' 	=> $userId,
									'ORDER_NO' 		=> $retstockdetails->ORDER_NO,
									'BRANCH_ID'		=> $tcPortfolioCode,
									'INVOICE_NO'	=> $retstockdetails->INVOICE_NO,
									'REMARKS' 		=> $retstockdetails->REMARKS[$i],
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
							$compStockData = $this->fetchCompanyStockData($retstockdetails->CATEGORY_ID[$i]);
							$restOfQty = 0;
							$restOfQtyCheck = 0;
							$retQuantity = $retstockdetails->RET_QUANTITY[$i];
							foreach ($compStockData as $selectOption) {
								$stockDetailsID			= $selectOption['STOCK_DETAILS_ID'];
								$compStockQty 			= $selectOption['QUANTITY'];
								$compStockCatPriceID 	= $selectOption['CAT_PRICE_ID'];
								$compStockSuppID 		= $selectOption['SUPPLIER_INFO_ID'];
								$compStockOderID 		= $selectOption['STOCK_ORDER_ID'];
								$compStockBuyPrice 		= $selectOption['BUY_PRICE'];
								$compStockAvgRate 		= $selectOption['AVG_RATE'];
								$compStockBranchID 		= $selectOption['BRANCH_ID'];
								
								if($compStockQty > $retQuantity) {
									$compStockTotalAmount	= $retQuantity * $retstockdetails->RET_BUY_PRICE[$i];
									$compStockNetAmount		= $compStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$compStockBuyQty		= $compStockQty - $retQuantity;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
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
																				ORDER_NO,
																				BRANCH_ID
																			)
																		VALUES
																			(
																				'".$this->getMaxStockDetailsID()."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $retQuantity)."',
																				'".str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i])."',
																				'".$compStockTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockNetAmount)."',
																				's',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				'',
																				'".$compStockBranchID."'
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
																				ORDER_NO,
																				BRANCH_ID
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockBuyQty)."',
																				'".str_replace(",", "", $compStockBuyPrice)."',
																				'".$compStockBuyTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockBuyNetAmount)."',
																				'b',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				'',
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
								} else if ($compStockQty < $retQuantity) {
									
									$compStockTotalAmount		= $compStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									
									
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
																				ORDER_NO,
																				BRANCH_ID
																			)
																		VALUES
																			(
																				'".$this->getMaxStockDetailsID()."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockQty)."',
																				'".str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i])."',
																				'".$compStockTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockNetAmount)."',
																				's',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				'',
																				'".$compStockBranchID."'
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
																				ORDER_NO,
																				BRANCH_ID
																			)
																		VALUES
																			(
																				'".$compStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$compStockCatPriceID."',
																				'".$compStockSuppID."',
																				'".str_replace(",", "", $compStockQty)."',
																				'".str_replace(",", "", $compStockBuyPrice)."',
																				'".$compStockBuyTotalAmount."',
																				'".str_replace(",", "", $compStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $compStockBuyNetAmount)."',
																				'b',
																				'block',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				'',
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
									$compStockTotalAmount		= $compStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
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
																			ORDER_NO,
																			BRANCH_ID
																		)
																	VALUES
																		(
																			'".$this->getMaxStockDetailsID()."',
																			'".$retstockdetails->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $retQuantity)."',
																			'".str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i])."',
																			'".$compStockTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockNetAmount)."',
																			's',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			'',
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
							
							
							
							
							
							/*$srStockData = $this->fetchCompanyStockData($retstockdetails->CATEGORY_ID[$i]);
							$restOfQty = 0;
							$restOfQtyCheck = 0;
							$retQuantity = $retstockdetails->RET_QUANTITY[$i];
							foreach ($srStockData as $selectOption) {
								$stockDetailsID			= $selectOption['STOCK_DETAILS_ID'];
								$srStockQty 			= $selectOption['QUANTITY'];
								$srStockCatPriceID 		= $selectOption['CAT_PRICE_ID'];
								$srStockOderID 			= $selectOption['STOCK_ORDER_ID'];
								$srStockBuyPrice 		= $selectOption['BUY_PRICE'];
								$srStockAvgRate 		= $selectOption['AVG_RATE'];
								
								if($srStockQty > $retQuantity) {
									$srStockTotalAmount	= $retQuantity * $retstockdetails->RET_BUY_PRICE[$i];
									$srStockNetAmount		= $srStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$srStockBuyQty		= $srStockQty - $retQuantity;
									$srStockBuyTotalAmount	= $srStockBuyQty * $srStockBuyPrice;
									$srStockBuyNetAmount		= $srStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									// sell entry section start
										$insertSaleQuery = "
															INSERT INTO 
																		i_sr_stock_dist_details
																			(	
																				SR_STOCK_DIST_ID,
																				CATEGORY_ID,
																				CAT_PRICE_ID,
																				SR_QUANTITY,
																				SR_SALE_PRICE,
																				SR_TOTAL_AMOUNT,
																				SR_AVG_RATE,
																				SR_DISCOUNT,
																				SR_NET_AMOUNT,
																				STATUS,
																				SOLD_FLAG,
																				BUSINESS_DATE,
																				RECORD_DATE,
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$srStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$srStockCatPriceID."',
																				'".str_replace(",", "", $retQuantity)."',
																				'".str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i])."',
																				'".$srStockTotalAmount."',
																				'".str_replace(",", "", $srStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $srStockNetAmount)."',
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
																		i_sr_stock_dist_details
																			(	
																				SR_STOCK_DIST_ID,
																				CATEGORY_ID,
																				CAT_PRICE_ID,
																				SR_QUANTITY,
																				SR_BUY_PRICE,
																				SR_TOTAL_AMOUNT,
																				SR_AVG_RATE,
																				SR_DISCOUNT,														
																				SR_NET_AMOUNT,
																				STATUS,
																				SOLD_FLAG,
																				BUSINESS_DATE,
																				RECORD_DATE,
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$srStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$srStockCatPriceID."',
																				'".str_replace(",", "", $srStockBuyQty)."',
																				'".str_replace(",", "", $srStockBuyPrice)."',
																				'".$srStockBuyTotalAmount."',
																				'".str_replace(",", "", $srStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $srStockBuyNetAmount)."',
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
										$updateSoldFlagSql = "UPDATE i_sr_stock_dist_details SET	
																			SOLD_FLAG='block'
																			WHERE i_sr_stock_dist_details.SR_STOCK_DIST_DETAILS_ID = '".$stockDetailsID."'
																	";
										$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
										$soldFlagSqlStatement->prepare();
										$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									break;
								} else if ($srStockQty < $retQuantity) {
									
									$srStockTotalAmount		= $srStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$srStockNetAmount			= $srStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$srStockBuyQty			= $srStockQty;
									$srStockBuyTotalAmount	= $srStockBuyQty * $srStockBuyPrice;
									$srStockBuyNetAmount		= $srStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									
									
										if($restOfQtyCheck == 0){
											$restOfQty = $retQuantity - $srStockQty;
											$retQuantity = $restOfQty;
										} else {
											$restOfQty = $restOfQty - $srStockQty;
											$retQuantity = $restOfQty;
										}
									// sell entry section start
										$insertSaleQuery = "
															INSERT INTO 
																		i_sr_stock_dist_details
																			(	
																				STOCK_ORDER_ID,
																				CATEGORY_ID,
																				CAT_PRICE_ID,
																				SR_QUANTITY,
																				SR_SALE_PRICE,
																				SR_TOTAL_AMOUNT,
																				SR_AVG_RATE,
																				SR_DISCOUNT,														
																				SR_NET_AMOUNT,
																				STATUS,
																				SOLD_FLAG,
																				BUSINESS_DATE,
																				RECORD_DATE,
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$srStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$srStockCatPriceID."',
																				'".$srStockSuppID."',
																				'".str_replace(",", "", $srStockQty)."',
																				'".str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i])."',
																				'".$srStockTotalAmount."',
																				'".str_replace(",", "", $srStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $srStockNetAmount)."',
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
																		i_sr_stock_dist_details
																			(	
																				SR_STOCK_DIST_ID,
																				CATEGORY_ID,
																				CAT_PRICE_ID,
																				SR_QUANTITY,
																				SR_BUY_PRICE,
																				SR_TOTAL_AMOUNT,
																				SR_AVG_RATE,
																				SR_DISCOUNT,														
																				SR_NET_AMOUNT,
																				STATUS,
																				SOLD_FLAG,
																				BUSINESS_DATE,
																				RECORD_DATE,
																				OPERATE_BY
																			)
																		VALUES
																			(
																				'".$srStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$srStockCatPriceID."',
																				'".str_replace(",", "", $srStockQty)."',
																				'".str_replace(",", "", $srStockBuyPrice)."',
																				'".$srStockBuyTotalAmount."',
																				'".str_replace(",", "", $srStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $srStockBuyNetAmount)."',
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
										$updateSoldFlagSql = "UPDATE i_sr_stock_dist_details SET	
																			SOLD_FLAG='block'
																			WHERE i_sr_stock_dist_details.SR_STOCK_DIST_DETAILS_ID = '".$stockDetailsID."'
																	";
										$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
										$soldFlagSqlStatement->prepare();
										$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									$restOfQtyCheck++;
								} else {
									$srStockTotalAmount		= $srStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$srStockNetAmount			= $srStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$srStockBuyQty			= $srStockQty;
									$srStockBuyTotalAmount	= $srStockBuyQty * $srStockBuyPrice;
									$srStockBuyNetAmount		= $srStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									// sell entry section start
									$insertSaleQuery = "
														INSERT INTO 
																	i_sr_stock_dist_details
																		(	
																			SR_STOCK_DIST_ID,
																			CATEGORY_ID,
																			CAT_PRICE_ID,
																			SR_QUANTITY,
																			SR_SALE_PRICE,
																			SR_TOTAL_AMOUNT,
																			SR_AVG_RATE,
																			SR_DISCOUNT,														
																			SR_NET_AMOUNT,
																			STATUS,
																			SOLD_FLAG,
																			BUSINESS_DATE,
																			RECORD_DATE,
																			OPERATE_BY
																		)
																	VALUES
																		(
																			'".$srStockOderID."',
																			'".$retstockdetails->CATEGORY_ID[$i]."',
																			'".$srStockCatPriceID."',
																			'".str_replace(",", "", $retQuantity)."',
																			'".str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i])."',
																			'".$srStockTotalAmount."',
																			'".str_replace(",", "", $srStockAvgRate)."',
																			'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																			'".str_replace(",", "", $srStockNetAmount)."',
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
							}*/
							
							
							
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
		public function saveRETStockReturnDetails($retstockdetails) {
			//return 1;
			//echo '<pre>';print_r($retstockdetails);die();
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
			$tcPortfolioCode	= $retstockdetails->BRANCH_ID;
			for($i = 0; $i < sizeof($retstockdetails->CATEGORY_ID); $i++) {
				$modelID	= $retstockdetails->CATEGORY_ID[$i];
				if(isset($retstockdetails->CATEGORY_ID[$i])) {
					$qty		= $retstockdetails->RET_QUANTITY[$i];
					$rate		= $retstockdetails->RET_BUY_PRICE[$i];
					$tcAmount 	= $retstockdetails->RET_TOTAL_AMOUNT[$i];
					$data = array(
									'RET_STOCK_DIST_ID' => $retstockdetails->RET_STOCK_DIST_ID,
									'CATEGORY_ID' 		=> $retstockdetails->CATEGORY_ID[$i],
									'CAT_PRICE_ID' 		=> $retstockdetails->CAT_PRICE_ID[$i],
									'RET_QUANTITY' 		=> str_replace(",", "", $retstockdetails->RET_QUANTITY[$i]),
									'RET_BUY_PRICE' 	=> str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i]),
									'RET_TOTAL_AMOUNT' 	=> str_replace(",", "", $retstockdetails->RET_TOTAL_AMOUNT[$i]),
									'RET_AVG_RATE' 		=> str_replace(",", "", $retstockdetails->RET_AVG_RATE[$i]),
									'REMARKS' 			=> $retstockdetails->REMARKS[$i],
									'RET_DISCOUNT' 		=> str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i]),
									'RET_DISCOUNT_RECEIVE' => str_replace(",", "", $retstockdetails->RET_DISCOUNT_RECEIVE[$i]),
									'RET_NET_AMOUNT' 	=> str_replace(",", "", $retstockdetails->RET_NET_AMOUNT[$i]),
									'STATUS' 		=> 'r',
									'SOLD_FLAG'	=> '',
									'BUSINESS_DATE' => $businessDate,
									'RECORD_DATE' 	=> $recDate,
									'OPERATE_BY' 	=> $userId,
									'ORDER_NO'		=> $retstockdetails->ORDER_NO,
									'BRANCH_ID'		=> $tcPortfolioCode,
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
							$retStockData = $this->fetchRetCurrentStockData($retstockdetails->CATEGORY_ID[$i]);
							$restOfQty = 0;
							$restOfQtyCheck = 0;
							$returnQuantity = $retstockdetails->RET_QUANTITY[$i];
							foreach ($retStockData as $selectOption) {
								$stockDetailsID			= $selectOption['RET_STOCK_DIST_DETAILS_ID'];
								$retStockQty 			= $selectOption['RET_QUANTITY'];
								$retStockCatPriceID 	= $selectOption['CAT_PRICE_ID'];
								$retStockOderID 		= $selectOption['RET_STOCK_DIST_ID'];
								$retStockBuyPrice 		= $selectOption['RET_BUY_PRICE'];
								$retStockAvgRate 		= $selectOption['RET_AVG_RATE'];
								$retStockOrderNo 		= $selectOption['ORDER_NO'];
								$retStockBranchID 		= $selectOption['BRANCH_ID'];
								$retRemarks				= $selectOption['REMARKS'];
								if($retStockQty > $returnQuantity) {
									$retStockTotalAmount	= $returnQuantity * $retstockdetails->RET_BUY_PRICE[$i];
									$retStockNetAmount		= $retStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$retStockBuyQty			= $retStockQty - $returnQuantity;
									$retStockBuyTotalAmount	= $retStockBuyQty * $retStockBuyPrice;
									$retStockBuyNetAmount	= $retStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									// buy entry section start
										$insertBuyQuery = "
															INSERT INTO 
																		i_retailer_stock_dist_details
																			(	
																				RET_STOCK_DIST_ID,
																				CATEGORY_ID,
																				CAT_PRICE_ID,
																				RET_QUANTITY,
																				RET_BUY_PRICE,
																				RET_TOTAL_AMOUNT,
																				RET_AVG_RATE,
																				RET_DISCOUNT,														
																				RET_NET_AMOUNT,
																				STATUS,
																				SOLD_FLAG,
																				BUSINESS_DATE,
																				RECORD_DATE,
																				OPERATE_BY,
																				ORDER_NO,
																				BRANCH_ID,
																				REMARKS
																			)
																		VALUES
																			(
																				'".$retStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$retStockCatPriceID."',
																				'".str_replace(",", "", $retStockBuyQty)."',
																				'".str_replace(",", "", $retStockBuyPrice)."',
																				'".$retStockBuyTotalAmount."',
																				'".str_replace(",", "", $retStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $retStockBuyNetAmount)."',
																				'b',
																				'',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				'".$retStockOrderNo."',
																				'".$retStockBranchID."',
																				'".$retRemarks."'
																			)
																";
										$buyQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertBuyQuery);
										$buyQueryStatement->prepare();
										$buyQueryStatement->execute();
									// buy entry section end
									
									//previous buy row sold_flag update start
										$updateSoldFlagSql = "UPDATE i_retailer_stock_dist_details SET	
																			SOLD_FLAG='block'
																			WHERE i_retailer_stock_dist_details.RET_STOCK_DIST_DETAILS_ID = '".$stockDetailsID."'
																	";
										$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
										$soldFlagSqlStatement->prepare();
										$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									break;
								} else if ($retStockQty < $returnQuantity) {
									$retStockTotalAmount		= $retStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$retStockNetAmount			= $retStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$retStockBuyQty			= $retStockQty;
									$retStockBuyTotalAmount	= $retStockBuyQty * $retStockBuyPrice;
									$retStockBuyNetAmount		= $retStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
										if($restOfQtyCheck == 0){
											$restOfQty = $returnQuantity - $retStockQty;
											$returnQuantity = $restOfQty;
										} else {
											$restOfQty = $restOfQty - $retStockQty;
											$returnQuantity = $restOfQty;
										}
										// buy entry section start
										$insertBuyQuery = "
															INSERT INTO 
																		i_retailer_stock_dist_details
																			(	
																				RET_STOCK_DIST_ID,
																				CATEGORY_ID,
																				CAT_PRICE_ID,
																				RET_QUANTITY,
																				RET_BUY_PRICE,
																				RET_TOTAL_AMOUNT,
																				RET_AVG_RATE,
																				RET_DISCOUNT,														
																				RET_NET_AMOUNT,
																				STATUS,
																				SOLD_FLAG,
																				BUSINESS_DATE,
																				RECORD_DATE,
																				OPERATE_BY,
																				ORDER_NO,
																				BRANCH_ID,
																				REMARKS
																			)
																		VALUES
																			(
																				'".$retStockOderID."',
																				'".$retstockdetails->CATEGORY_ID[$i]."',
																				'".$retStockCatPriceID."',
																				'".str_replace(",", "", $retStockQty)."',
																				'".str_replace(",", "", $retStockBuyPrice)."',
																				'".$retStockBuyTotalAmount."',
																				'".str_replace(",", "", $retStockAvgRate)."',
																				'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																				'".str_replace(",", "", $retStockBuyNetAmount)."',
																				'b',
																				'block',
																				'".$businessDate."',
																				'".$recDate."',
																				'".$userId."',
																				'".$retStockOrderNo."',
																				'".$retStockBranchID."',
																				'".$retRemarks."'
																			)
																";
										$buyQueryStatement = $this->tableGateway->getAdapter()->createStatement($insertBuyQuery);
										$buyQueryStatement->prepare();
										$buyQueryStatement->execute();
									// buy entry section end
									//previous buy row sold_flag update start
										$updateSoldFlagSql = "UPDATE i_retailer_stock_dist_details SET	
																			SOLD_FLAG='block'
																			WHERE i_retailer_stock_dist_details.RET_STOCK_DIST_DETAILS_ID = '".$stockDetailsID."'
																	";
										$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
										$soldFlagSqlStatement->prepare();
										$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									$restOfQtyCheck++;
								} else {
									$retStockTotalAmount		= $retStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$retStockNetAmount			= $retStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$retStockBuyQty			= $retStockQty;
									$retStockBuyTotalAmount	= $retStockBuyQty * $retStockBuyPrice;
									$retStockBuyNetAmount		= $retStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									//previous buy row sold_flag update start
									$updateSoldFlagSql = "UPDATE i_retailer_stock_dist_details SET	
																		SOLD_FLAG='block'
																		WHERE i_retailer_stock_dist_details.RET_STOCK_DIST_DETAILS_ID = '".$stockDetailsID."'
																";
									$soldFlagSqlStatement = $this->tableGateway->getAdapter()->createStatement($updateSoldFlagSql);
									$soldFlagSqlStatement->prepare();
									$soldFlagSqlStatement->execute();
									//previous buy row sold_flag update end
									break;
								}
							}
							
							// company stock update while retailer return start //
							$compStockData = $this->fetchCompanyStockData($retstockdetails->CATEGORY_ID[$i]);
							$restOfQty = 0;
							$restOfQtyCheck = 0;
							$returnQuantity = $retstockdetails->RET_QUANTITY[$i];
							foreach ($compStockData as $selectOption) {
								$stockDetailsID			= $selectOption['STOCK_DETAILS_ID'];
								$compStockQty 			= $selectOption['QUANTITY'];
								$compStockCatPriceID 	= $selectOption['CAT_PRICE_ID'];
								$compStockSuppID 		= $selectOption['SUPPLIER_INFO_ID'];
								$compStockOderID 		= $selectOption['STOCK_ORDER_ID'];
								$compStockBuyPrice 		= $selectOption['BUY_PRICE'];
								$compStockAvgRate 		= $selectOption['AVG_RATE'];
								$compStockBranchID 		= $selectOption['BRANCH_ID'];
								$remarks				= $selectOption['REMARKS'];
								
								if($compStockQty > $returnQuantity) {
									//echo 'company stock return quantity theke boro';die();
									//echo 'elkane dhukce';die();
									$compStockTotalAmount	= $returnQuantity * $retstockdetails->RET_BUY_PRICE[$i];
									$compStockNetAmount		= $compStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$compStockBuyQty		= $compStockQty + $returnQuantity;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
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
																			BRANCH_ID,
																			REMARKS
																		)
																	VALUES
																		(
																			'".$compStockOderID."',
																			'".$retstockdetails->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $compStockBuyQty)."',
																			'".str_replace(",", "", $compStockBuyPrice)."',
																			'".$compStockBuyTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockBuyNetAmount)."',
																			'b',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			'".$compStockBranchID."',
																			'".$remarks."'
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
									//echo 'company stock return quantity theke choto';die();
									$compStockTotalAmount		= $compStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
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
																			BRANCH_ID,
																			REMARKS
																		)
																	VALUES
																		(
																			'".$compStockOderID."',
																			'".$retstockdetails->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $compStockQty)."',
																			'".str_replace(",", "", $compStockBuyPrice)."',
																			'".$compStockBuyTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockBuyNetAmount)."',
																			'b',
																			'block',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			'".$compStockBranchID."',
																			'".$remarks."'
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
									//echo 'else e dhukce';die();
									$compStockTotalAmount		= $compStockQty * $retstockdetails->RET_BUY_PRICE[$i];
									$compStockNetAmount			= $compStockTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
									$compStockBuyQty			= $compStockQty + $returnQuantity;
									$compStockBuyTotalAmount	= $compStockBuyQty * $compStockBuyPrice;
									$compStockBuyNetAmount		= $compStockBuyTotalAmount - $retstockdetails->RET_DISCOUNT[$i];
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
																			BRNACH_ID,
																			REMAKRS
																		)
																	VALUES
																		(
																			'".$compStockOderID."',
																			'".$retstockdetails->CATEGORY_ID[$i]."',
																			'".$compStockCatPriceID."',
																			'".$compStockSuppID."',
																			'".str_replace(",", "", $compStockBuyQty)."',
																			'".str_replace(",", "", $retstockdetails->RET_BUY_PRICE[$i])."',
																			'".$compStockTotalAmount."',
																			'".str_replace(",", "", $compStockAvgRate)."',
																			'".str_replace(",", "", $retstockdetails->RET_DISCOUNT[$i])."',
																			'".str_replace(",", "", $compStockNetAmount)."',
																			'b',
																			'',
																			'".$businessDate."',
																			'".$recDate."',
																			'".$userId."',
																			'".$compStockBranchID."',
																			'".$remarks."'
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
							// company stock update while retailer return start //
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
		
		public function fetchCompanyStockData($catId) {
			$select = "
						SELECT SD.STOCK_DETAILS_ID,
							   SD.QUANTITY AS QUANTITY,
							   SD.CAT_PRICE_ID,
							   SD.SUPPLIER_INFO_ID,
							   SD.STOCK_ORDER_ID,
							   SD.BUY_PRICE,
							   SD.AVG_RATE,
							   SD.BRANCH_ID,
							   SD.REMARKS
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
		public function fetchSalesStockWiseModelDetails($input) {
			if(!empty($input)){
				$getTblDataSql   = "
								SELECT DISTINCT retstock.CATEGORY_ID, ls_category.CATEGORY_NAME
								FROM ls_category, i_retailer_stock_dist_details retstock
								WHERE LOWER( ls_category.CATEGORY_NAME ) = '".strtolower($input)."'
								AND active_inactive = 'yes'
								AND retstock.CATEGORY_ID = ls_category.CATEGORY_ID
								AND (SELECT SUM( RETSD.RET_QUANTITY )
								FROM i_retailer_stock_dist_details RETSD
								WHERE RETSD.STATUS = 'b'
								AND RETSD.SOLD_FLAG = ''
								AND retstock.CATEGORY_ID = RETSD.CATEGORY_ID
								) >0
								ORDER BY ls_category.CATEGORY_NAME ASC
								LIMIT 0 , 30
							   ";
			}else{
			$getTblDataSql   = "
								SELECT DISTINCT retstock.CATEGORY_ID, ls_category.CATEGORY_NAME
								FROM ls_category, i_retailer_stock_dist_details retstock
								WHERE active_inactive = 'yes'
								AND retstock.CATEGORY_ID = ls_category.CATEGORY_ID
								AND (SELECT SUM( RETSD.RET_QUANTITY )
								FROM i_retailer_stock_dist_details RETSD
								WHERE RETSD.STATUS = 'b'
								AND RETSD.SOLD_FLAG = ''
								AND retstock.CATEGORY_ID = RETSD.CATEGORY_ID
								) >0
								ORDER BY ls_category.CATEGORY_NAME ASC
								
							   ";

			}
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
		public function fetchRetSumofStockQuantity($catId) {
			$getTblDataSql   = "
								SELECT sum(RETSD.RET_QUANTITY) AS RET_QUANTITY,ls_cat_price.SALE_PRICE,ls_cat_price.CAT_PRICE_ID,ls_category.COA_CODE,gs_coa.COA_NAME,ls_cat_price.BUY_PRICE
								FROM i_retailer_stock_dist_details RETSD,ls_category, ls_cat_price, gs_coa
								WHERE RETSD.CATEGORY_ID =  '".$catId."'
								AND RETSD.STATUS = 'b'
								AND RETSD.SOLD_FLAG = ''
								AND ls_category.active_inactive = 'yes'
								AND RETSD.CATEGORY_ID = ls_category.CATEGORY_ID								
								AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
								AND gs_coa.COA_CODE = ls_category.COA_CODE
								AND ls_cat_price.END_DATE IS NULL
								";	/*SELECT sum(SD.QUANTITY) AS QUANTITY
										FROM i_stock_details SD
										WHERE SD.CATEGORY_ID =  '".$catId."'
										AND SD.STATUS = 'b'
										AND SD.SOLD_FLAG = ''*/
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchRetCurrentStockData($catId) {
			$select = "
						SELECT RETSD.RET_STOCK_DIST_DETAILS_ID,
							   RETSD.RET_QUANTITY AS RET_QUANTITY,
							   RETSD.CAT_PRICE_ID,
							   RETSD.RET_STOCK_DIST_ID,
							   RETSD.RET_BUY_PRICE,
							   RETSD.RET_AVG_RATE,
							   RETSD.ORDER_NO,
							   RETSD.BRANCH_ID,
							   RETSD.REMARKS
						FROM i_retailer_stock_dist_details RETSD
						WHERE RETSD.CATEGORY_ID =  '".$catId."'
						AND RETSD.STATUS = 'b'
						AND RETSD.SOLD_FLAG = ''
						ORDER BY RETSD.CAT_PRICE_ID ASC
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
		public function fetchSellDetailsForInvoicePrint($retStockOrderID,$invoiceNo) {
			$select = "		
						SELECT RSD.RET_STOCK_DIST_DETAILS_ID,
							   RSD.RET_QUANTITY AS QUANTITY,
							   RSD.RET_BUY_PRICE,
							   RSD.RET_AVG_RATE,
							   RSD.RET_DISCOUNT,
							   RSD.RET_TOTAL_AMOUNT,							   
							   RSD.RET_NET_AMOUNT,
							   ls_category.CATEGORY_NAME PRODUCT_NAME
						FROM i_retailer_stock_dist_details RSD, ls_category, ls_cat_price
						WHERE RSD.RET_STOCK_DIST_ID =  '".$retStockOrderID."'
						AND ls_category.active_inactive = 'yes'
						AND RSD.CATEGORY_ID = ls_category.CATEGORY_ID								
						AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
						AND ls_cat_price.END_DATE IS NULL
						ORDER BY RSD.RET_STOCK_DIST_DETAILS_ID ASC
			";
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
				$purchaseDataDetails['STOCK_DETAILS_ID'][] = $purchaseDetails["RET_STOCK_DIST_DETAILS_ID"];
				$purchaseDataDetails['QUANTITY'][] 		= $purchaseDetails["QUANTITY"];
				$purchaseDataDetails['BUY_PRICE'][] 	= $purchaseDetails["RET_BUY_PRICE"];
				$purchaseDataDetails['AVG_RATE'][] 		= $purchaseDetails["RET_AVG_RATE"];
				$purchaseDataDetails['DISCOUNT'][] 		= $purchaseDetails["RET_DISCOUNT"];
				$purchaseDataDetails['TOTAL_AMOUNT'][] 	= $purchaseDetails["RET_TOTAL_AMOUNT"];
				$purchaseDataDetails['NET_AMOUNT'][] 	= $purchaseDetails["RET_NET_AMOUNT"];
				$purchaseDataDetails['PRODUCT_NAME'][] 	= $purchaseDetails["PRODUCT_NAME"];
			}
			return $purchaseDataDetails;
		}
		
	}
?>	