<?php
	namespace Accounts\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class OpeningBalanceEntry implements InputFilterAwareInterface {
		public $BRANCH_ID;
		public $tranDateTo;
		public $paymentEntry;
		public $coa_head;
		public $coa_code;
		public $COAType;
		public $amount;
		public $NumberOfRows;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->BRANCH_ID 			= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->tranDateTo 			= (!empty($data['tranDateTo'])) ? $data['tranDateTo'] : null;
			$this->paymentEntry 		= (!empty($data['paymentEntry'])) ? $data['paymentEntry'] : null;
			$this->coa_head 			= (!empty($data['coa_head'])) ? $data['coa_head'] : null;
			$this->coa_code 			= (!empty($data['coa_code'])) ? $data['coa_code'] : null;
			$this->COAType 				= (!empty($data['COAType'])) ? $data['COAType'] : null;
			$this->amount 				= (!empty($data['amount'])) ? $data['amount'] : null;
			$this->NumberOfRows 		= (!empty($data['NumberOfRows'])) ? $data['NumberOfRows'] : null;
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