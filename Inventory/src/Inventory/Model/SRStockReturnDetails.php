<?php
	namespace Inventory\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class SRStockReturnDetails implements InputFilterAwareInterface {
		public $SR_STOCK_RET_DET_ID;
		public $SR_STOCK_RETURN_ID;
		public $CATEGORY_ID;
		public $CAT_PRICE_ID;
		public $SR_QUANTITY;
		public $SR_BUY_PRICE;
		public $SR_SALE_PRICE;
		public $SR_TOTAL_AMOUNT;
    	public $SR_AVG_RATE;    
		public $SR_DISCOUNT;
    	public $SR_NET_AMOUNT;
		public $STATUS;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->SR_STOCK_RET_DET_ID 	= (!empty($data['SR_STOCK_RET_DET_ID'])) ? $data['SR_STOCK_RET_DET_ID'] : null;
			$this->SR_STOCK_RETURN_ID 			= (!empty($data['SR_STOCK_RETURN_ID'])) ? $data['SR_STOCK_RETURN_ID'] : null;
			$this->CATEGORY_ID 					= (!empty($data['CATEGORY_ID'])) ? $data['CATEGORY_ID'] : null;
			$this->CAT_PRICE_ID 				= (!empty($data['CAT_PRICE_ID'])) ? $data['CAT_PRICE_ID'] : null;
			$this->SR_QUANTITY 					= (!empty($data['SR_QUANTITY'])) ? $data['SR_QUANTITY'] : null;
			$this->SR_BUY_PRICE 				= (!empty($data['SR_BUY_PRICE'])) ? $data['SR_BUY_PRICE'] : null;
			$this->SR_SALE_PRICE 				= (!empty($data['SR_SALE_PRICE'])) ? $data['SR_SALE_PRICE'] : null;
			$this->SR_TOTAL_AMOUNT 				= (!empty($data['SR_TOTAL_AMOUNT'])) ? $data['SR_TOTAL_AMOUNT'] : null;
			$this->SR_AVG_RATE 					= (!empty($data['SR_AVG_RATE'])) ? $data['SR_AVG_RATE'] : null;
			$this->SR_DISCOUNT 					= (!empty($data['SR_DISCOUNT'])) ? $data['SR_DISCOUNT'] : null;
			$this->SR_NET_AMOUNT 				= (!empty($data['SR_NET_AMOUNT'])) ? $data['SR_NET_AMOUNT'] : null;
			$this->STATUS 						= (!empty($data['STATUS'])) ? $data['STATUS'] : null;
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
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	