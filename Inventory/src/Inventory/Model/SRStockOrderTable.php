<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class SRStockOrderTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function stockOrderExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		public function saveSRStockOrder($srstockorder) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$data = array(
							'ORDER_NO' 				=> $srstockorder->ORDER_NO,
							'EMPLOYEE_ID' 			=> $srstockorder->EMPLOYEE_ID,
							'SR_TOTAL_AMOUNT' 		=> str_replace(",", "", $srstockorder->SR_TOTAL_AMOUNT),
							'SR_DISCOUNT_AMOUNT' 	=> str_replace(",", "", $srstockorder->SR_DISCOUNT_AMOUNT),
							//'DISCOUNT_TYPE' 	=> str_replace(",", "", $stockorder->DISCOUNT_TYPE),
							'BUSINESS_DATE' => $businessDate,
							'RECORD_DATE' 	=> $recDate,
							'OPERATE_BY' 	=> $userId,
						);
			//echo '<pre>';print_r($data);die();
			$existCheckData = array(
				'ORDER_NO' => $srstockorder->ORDER_NO,
			);
			if($this->tableGateway->insert($data)) {
				$stockorderSql = $this->stockOrderExist($existCheckData);
				$stockOrderID = (int) $stockorderSql->SR_STOCK_DIST_ID;
				return $stockOrderID;
				//$transStatus = 1;
			} else {
				$transStatus = 0;
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
		
	}
?>	