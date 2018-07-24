<?php
	namespace Ibcia\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\Sql\Expression;
	use Zend\Db\ResultSet\ResultSet;
		
	class MarketPriceHistoryTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll() {
			$resultSet = $this->tableGateway->select();
			return $resultSet;
		}
		
		public function isTodaysMarketInfoExist($businessDate) {
			$selectScripMarketInfo = "	
										SELECT 	
												COUNT(INSTRUMENT_DETAILS_ID) MARKET_PRICE_EXIST
										FROM 	
												P_CAP_MKT_PRICE_HISTORY
										WHERE 	
												P_CAP_MKT_PRICE_HISTORY.BUSINESS_DATE = to_date('{$businessDate}','dd-mm-yyyy')
									";
			$selectScripMarketInfoStatement = $this->tableGateway->getAdapter()->createStatement($selectScripMarketInfo);
			$selectScripMarketInfoStatement->prepare();
			$selectScripMarketInfoResult 	= $selectScripMarketInfoStatement->execute();
			
			if ($selectScripMarketInfoResult instanceof ResultInterface && $selectScripMarketInfoResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectScripMarketInfoResult);
			}
			
			return $resultSet;
		}
		
		public function getInstrumentList($marketDetailsId, $isActive, $isApproved, $isListed, $sectorId = 0) {
			$selectInstrumentInfo = "	
										SELECT 	
												INSTDET.INSTRUMENT_DETAILS_ID,
												INSTDET.INSTRUMENT_NAME,
												MWINST.SYMBOL
										FROM 	
												LS_INSTRUMENT_DETAILS INSTDET,
												LS_MARKET_WISE_INSTRUMENT MWINST
										WHERE 	
												INSTDET.INSTRUMENT_DETAILS_ID		= MWINST.INSTRUMENT_DETAILS_ID
										AND		MWINST.MARKET_DETAILS_ID			= ".$marketDetailsId."
										AND		lower(MWINST.ACTIVE_OR_INACTIVE_FLAG)		= '".strtolower($isActive)."'
										AND		lower(MWINST.APPROVE_OR_DISAPPROVE_FLAG)	= '".strtolower($isApproved)."'
										AND		lower(MWINST.LISTED_OR_NONLISTED_FLAG) = '".strtolower($isListed)."'
									";
			$selectInstrumentInfo .= ($sectorId > 0) ? 'AND		lower(MWINST.LISTED_OR_NONLISTED_FLAG) = '.$sectorId : '';
			//echo "<pre>";
			//echo $selectInstrumentInfo;
			//echo "<br/>";
			$selectInstrumentInfoStatement 	= $this->tableGateway->getAdapter()->createStatement($selectInstrumentInfo);
			$selectInstrumentInfoStatement->prepare();
			$selectInstrumentInfoResult 	= $selectInstrumentInfoStatement->execute();
			
			$instInfo = array();
			if ($selectInstrumentInfoResult instanceof ResultInterface && $selectInstrumentInfoResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectInstrumentInfoResult);
				
				foreach ($resultSet as $iDetail) :
				$instInfo['INSTRUMENT_DETAILS_ID'][]	= $iDetail->INSTRUMENT_DETAILS_ID;
				$instInfo['INSTRUMENT_NAME'][]			= $iDetail->INSTRUMENT_NAME;
				$instInfo['SYMBOL'][]					= $iDetail->SYMBOL;
				endforeach;
			}
			
			return $instInfo;
		}
		
		public function getMarketPriceHistory($id) {
			$id = (int) $id;
			
			$rowSet = $this->tableGateway->select(array('CAP_MKT_PRICE_HISTORY_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function saveMarketPriceHistory(MarketPriceHistory $marketPriceHistory) {
			$id 				= (int) $marketPriceHistory->CAP_MKT_PRICE_HISTORY_ID;
			
			$data = array(
				'MARKET_DETAILS_ID' 		=> $marketPriceHistory->MARKET_DETAILS_ID,
				'INSTRUMENT_DETAILS_ID' 	=> $marketPriceHistory->INSTRUMENT_DETAILS_ID,
				'LAST_TRADE' 				=> $marketPriceHistory->LAST_TRADE,
				'LAST_UPDATE' 				=> $marketPriceHistory->LAST_UPDATE,
				'OPEN_PRICE' 				=> $marketPriceHistory->OPEN_PRICE,
				'YCP' 						=> $marketPriceHistory->YCP,
				'HIGH_PRICE' 				=> $marketPriceHistory->HIGH_PRICE,
				'LOW_PRICE' 				=> $marketPriceHistory->LOW_PRICE,
				'CLOSE_PRICE' 				=> $marketPriceHistory->CLOSE_PRICE,
				'TOTAL_TRADE' 				=> $marketPriceHistory->TOTAL_TRADE,
				'VOLUME' 					=> $marketPriceHistory->VOLUME,
				'VALUE_IN_MN' 				=> $marketPriceHistory->VALUE_IN_MN,
				'MARKET_CAPITAL' 			=> $marketPriceHistory->MARKET_CAPITAL,
				'BUSINESS_DATE' 			=> new Expression("to_date('$marketPriceHistory->BUSINESS_DATE', 'dd-mm-yyyy')"),
				'RECORD_DATE' 				=> new Expression("to_date('$marketPriceHistory->RECORD_DATE', 'dd-mm-yyyy hh:mi:ss')"),
				'OPERATE_BY' 				=> $marketPriceHistory->OPERATE_BY,
			);
			//echo "<pre>"; print_r($data); die();
			if($this->tableGateway->insert($data)) {
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