<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class RETStockOrderTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function stockOrderExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		public function saveRETStockOrder($retstockorder) {
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
							'RET_TOT_DIS_RECEIVE' 	=> str_replace(",", "", $retstockorder->RET_TOT_DIS_RECEIVE),
							'RET_LESS_DESCRIPTION'	=> $retstockorder->RET_LESS_DESCRIPTION,
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
			}
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
							'RET_TOT_DIS_RECEIVE' 	=> str_replace(",", "", $retstockorder->RET_TOT_DIS_RECEIVE),
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
		public function getRetailerOrderInfoForInvoice($invoiceNo) {
			$select = "		
						SELECT DISTINCT RS.RET_STOCK_DIST_ID, RS.ORDER_NO, RS.RET_TOTAL_AMOUNT, RS.RET_DISCOUNT_AMOUNT, RS.BUSINESS_DATE, RS.RETAILER_ID
						FROM i_retailer_stock_dist RS, i_retailer_stock_dist_details RSD
						WHERE RSD.INVOICE_NO = '".$invoiceNo."'
						AND RSD.RET_STOCK_DIST_ID = RS.RET_STOCK_DIST_ID
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
		
	}
?>	