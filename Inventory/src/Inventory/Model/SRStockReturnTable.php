<?php
	namespace Inventory\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class SRStockReturnTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function stockReturnExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		public function saveSRStockReturn($srstockreturn) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$data = array(
							'ORDER_NO' 				=> $srstockreturn->ORDER_NO,
							'EMPLOYEE_ID' 			=> $srstockreturn->EMPLOYEE_ID,
							'SR_TOTAL_AMOUNT' 		=> str_replace(",", "", $srstockreturn->SR_TOTAL_AMOUNT),
							'SR_DISCOUNT_AMOUNT' 	=> str_replace(",", "", $srstockreturn->SR_DISCOUNT_AMOUNT),
							//'DISCOUNT_TYPE' 	=> str_replace(",", "", $stockreturn->DISCOUNT_TYPE),
							'BUSINESS_DATE' => $businessDate,
							'RECORD_DATE' 	=> $recDate,
							'OPERATE_BY' 	=> $userId,
						);
			//echo '<pre>';print_r($data);die();
			$existCheckData = array(
				'ORDER_NO' => $srstockreturn->ORDER_NO,
			);
			if($this->tableGateway->insert($data)) {
				$stockreturnSql = $this->stockReturnExist($existCheckData);
				$stockReturnID = (int) $stockreturnSql->SR_STOCK_DIST_ID;
				return $stockReturnID;
				//$transStatus = 1;
			} else {
				$transStatus = 0;
			}
		}
		public function updateSRStockReturn($stockreturn) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("Y-m-d", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$transStatus   		= (int) 0;
			$id = (int) $stockreturn->STOCK_ORDER_ID;
			$cond = $stockreturn->COND;
			$updateStockReturnSql = '';
			if($cond == 'y'){
				$updateStockReturnSql = "UPDATE i_stock_return SET	
													TOTAL_AMOUNT = TOTAL_AMOUNT + '".str_replace(",", "", $stockreturn->TOTAL_AMOUNT)."',
													DISCOUNT_AMOUNT = DISCOUNT_AMOUNT + '".str_replace(",", "", $stockreturn->DISCOUNT_AMOUNT)."',
													NET_AMOUNT = NET_AMOUNT + '".str_replace(",", "", $stockreturn->NET_AMOUNT)."',
													PAYMENT_AMOUNT = PAYMENT_AMOUNT + '".str_replace(",", "", $stockreturn->PAYMENT_AMOUNT)."',
													REMAINING_AMOUNT= REMAINING_AMOUNT + '".str_replace(",", "", $stockreturn->REMAINING_AMOUNT)."'
													WHERE STOCK_ORDER_ID = '".$id."'
											";
			} else {
				$updateStockReturnSql = "UPDATE i_stock_return SET	
													TOTAL_AMOUNT='".str_replace(",", "", $stockreturn->TOTAL_AMOUNT)."',
													DISCOUNT_AMOUNT='".str_replace(",", "", $stockreturn->DISCOUNT_AMOUNT)."',
													NET_AMOUNT='".str_replace(",", "", $stockreturn->NET_AMOUNT)."',
													PAYMENT_AMOUNT='".str_replace(",", "", $stockreturn->PAYMENT_AMOUNT)."',
													REMAINING_AMOUNT='".str_replace(",", "", $stockreturn->REMAINING_AMOUNT)."'
													WHERE STOCK_ORDER_ID = '".$id."'
											";
			}
			$updateStockReturnStatement = $this->tableGateway->getAdapter()->createStatement($updateStockReturnSql);
			$updateStockReturnStatement->prepare();
			if(!$updateStockReturnResult = $updateStockReturnStatement->execute()) {
				return false;
			} else {
				return true;	
			}
		}
		public function getSRStockReturn($cond) {
			$select = "		
						SELECT 
								SO.STOCK_ORDER_ID,SO.ORDER_NO,TOTAL_AMOUNT,DISCOUNT_AMOUNT,NET_AMOUNT,PAYMENT_AMOUNT,REMAINING_AMOUNT
						FROM 
								i_stock_return SO
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