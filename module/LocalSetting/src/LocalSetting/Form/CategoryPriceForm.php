<?php
	namespace LocalSetting\Form;
	use Zend\Form\Form;
	
	class CategoryPriceForm extends Form {
		public function __construct($name = 'price') {
			parent::__construct($name);
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return getPriceData');
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
	}
?>	