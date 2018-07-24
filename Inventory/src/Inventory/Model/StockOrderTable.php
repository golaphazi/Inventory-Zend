<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class StockOrderTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function stockOrderExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		public function saveStockOrder($stockorder) {
			
			//return 1;die();
			//echo 'hi therere';die();
			//echo "<pre>"; print_r($stockorder); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$data = array(
							'ORDER_NO' 			=> $stockorder->ORDER_NO,
							'TOTAL_AMOUNT' 		=> str_replace(",", "", $stockorder->TOTAL_AMOUNT),
							'DISCOUNT_AMOUNT' 	=> str_replace(",", "", $stockorder->DISCOUNT_AMOUNT),
							'NET_AMOUNT' 		=> str_replace(",", "", $stockorder->NET_AMOUNT),
							'PAYMENT_AMOUNT' 	=> str_replace(",", "", $stockorder->PAYMENT_AMOUNT),
							'REMAINING_AMOUNT' 	=> str_replace(",", "", $stockorder->REMAINING_AMOUNT),
							'DISCOUNT_TYPE' 	=> str_replace(",", "", $stockorder->DISCOUNT_TYPE),
							'ORDER_TYPE'		=> 'buy',
							'BUSINESS_DATE' => $businessDate,
							'RECORD_DATE' 	=> $recDate,
							'OPERATE_BY' 	=> $userId,
							'LESS_DESCRIPTION' 	=> $stockorder->LESS_DESCRIPTION,
						);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'ORDER_NO' => $stockorder->ORDER_NO,
			);
			if($this->tableGateway->insert($data)) {
				$stockorderSql = $this->stockOrderExist($existCheckData);
				$stockOrderID = (int) $stockorderSql->STOCK_ORDER_ID;
				return $stockOrderID;
				//$transStatus = 1;
			} else {
				$transStatus = 0;
			}
		}
		public function saveStockReturnOrder($stockorder) {
			
			//return 1;die();
			//echo 'hi therere';die();
			//echo "<pre>"; print_r($stockorder); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$data = array(
							'ORDER_NO' 			=> $stockorder->ORDER_NO,
							'TOTAL_AMOUNT' 		=> str_replace(",", "", $stockorder->TOTAL_AMOUNT),
							'DISCOUNT_AMOUNT' 	=> str_replace(",", "", $stockorder->DISCOUNT_AMOUNT),
							'NET_AMOUNT' 		=> str_replace(",", "", $stockorder->NET_AMOUNT),
							'PAYMENT_AMOUNT' 	=> str_replace(",", "", $stockorder->PAYMENT_AMOUNT),
							'REMAINING_AMOUNT' 	=> str_replace(",", "", $stockorder->REMAINING_AMOUNT),
							'DISCOUNT_TYPE' 	=> str_replace(",", "", $stockorder->DISCOUNT_TYPE),
							'ORDER_TYPE'		=> 'return',
							'BUSINESS_DATE' => $businessDate,
							'RECORD_DATE' 	=> $recDate,
							'OPERATE_BY' 	=> $userId,
						);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'ORDER_NO' => $stockorder->ORDER_NO,
			);
			if($this->tableGateway->insert($data)) {
				$stockorderSql = $this->stockOrderExist($existCheckData);
				$stockOrderID = (int) $stockorderSql->STOCK_ORDER_ID;
				return $stockOrderID;
				//$transStatus = 1;
			} else {
				$transStatus = 0;
			}
		}
		public function updateStockOrder($stockorder) {
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
		public function getStockOrder($cond) {
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
		public function getStockOrderInformationForInvoice($invoiceNo) {
			$select = "		
						SELECT DISTINCT SO.STOCK_ORDER_ID, SO.ORDER_NO, SO.TOTAL_AMOUNT, DISCOUNT_AMOUNT, SO.NET_AMOUNT, PAYMENT_AMOUNT, REMAINING_AMOUNT, SO.BUSINESS_DATE, (SELECT DISTINCT SUPPLIER_INFO_ID FROM i_stock_details SD WHERE SD.INVOICE_NO = '".$invoiceNo."') AS SUPPLIER_INFO_ID
						FROM i_stock_order SO, i_stock_details SD
						WHERE SD.INVOICE_NO = '".$invoiceNo."'
						AND SO.STOCK_ORDER_ID = SD.STOCK_ORDER_ID
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