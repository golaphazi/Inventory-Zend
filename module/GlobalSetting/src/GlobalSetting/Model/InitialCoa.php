<?php
	namespace GlobalSetting\Model;
	
	class InitialCoa {
		
		public $OPERATE_BY;
		
		public function exchangeArray($data) {
			$this->OPERATE_BY 					= (!empty($data['OPERATE_BY'])) ? $data['OPERATE_BY'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>		