<?php
	namespace Ibcia\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class MarketPriceHistory implements InputFilterAwareInterface {
		public $CAP_MKT_PRICE_HISTORY_ID;
		public $MARKET_DETAILS_ID;
		public $INSTRUMENT_DETAILS_ID;
		public $LAST_TRADE;
		public $LAST_UPDATE;
		public $OPEN_PRICE;
		public $YCP;
		public $HIGH_PRICE;
		public $LOW_PRICE;
		public $CLOSE_PRICE;
		public $TOTAL_TRADE;
		public $VOLUME;
		public $VALUE_IN_MN;
		public $MARKET_CAPITAL;
		public $BUSINESS_DATE;
		public $RECORD_DATE;
		public $OPERATE_BY;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->CAP_MKT_PRICE_HISTORY_ID		= (!empty($data['CAP_MKT_PRICE_HISTORY_ID'])) ? $data['CAP_MKT_PRICE_HISTORY_ID'] : null;
			$this->MARKET_DETAILS_ID 			= (!empty($data['MARKET_DETAILS_ID'])) ? $data['MARKET_DETAILS_ID'] : null;
			$this->INSTRUMENT_DETAILS_ID		= (!empty($data['INSTRUMENT_DETAILS_ID'])) ? $data['INSTRUMENT_DETAILS_ID'] : null;
			$this->LAST_TRADE					= (!empty($data['LAST_TRADE'])) ? $data['LAST_TRADE'] : null;
			$this->LAST_UPDATE					= (!empty($data['LAST_UPDATE'])) ? $data['LAST_UPDATE'] : null;
			$this->OPEN_PRICE					= (!empty($data['OPEN_PRICE'])) ? $data['OPEN_PRICE'] : null;
			$this->YCP 							= (!empty($data['YCP'])) ? $data['YCP'] : null;
			$this->HIGH_PRICE 					= (!empty($data['HIGH_PRICE'])) ? $data['HIGH_PRICE'] : null;
			$this->LOW_PRICE					= (!empty($data['LOW_PRICE'])) ? $data['LOW_PRICE'] : null;
			$this->CLOSE_PRICE 					= (!empty($data['CLOSE_PRICE'])) ? $data['CLOSE_PRICE'] : null;
			$this->TOTAL_TRADE					= (!empty($data['TOTAL_TRADE'])) ? $data['TOTAL_TRADE'] : null;
			$this->VOLUME						= (!empty($data['VOLUME'])) ? $data['VOLUME'] : null;
			$this->VALUE_IN_MN 					= (!empty($data['VALUE_IN_MN'])) ? $data['VALUE_IN_MN'] : null;
			$this->MARKET_CAPITAL 				= (!empty($data['MARKET_CAPITAL'])) ? $data['MARKET_CAPITAL'] : null;
			$this->BUSINESS_DATE 				= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
			$this->RECORD_DATE 					= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;
			$this->OPERATE_BY 					= (!empty($data['OPERATE_BY'])) ? $data['OPERATE_BY'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
		
		public function setInputFilter(InputFilterInterface $inputFilter) {
			throw new \Exception("Not used");
		}
		
		public function getInputFilter() {
			if(!$this->inputFilter) {
				$inputFilter = new InputFilter();
				$factory	 = new InputFactory();
				
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'MARKET_DETAILS_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	