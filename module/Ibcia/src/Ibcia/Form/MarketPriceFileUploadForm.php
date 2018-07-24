<?php
	namespace Ibcia\Form;
	
	use Zend\InputFilter;
	use Zend\Form\Element;
	use Zend\Filter\File\Rename;
	use Zend\Form\Form;
		
	class MarketPriceFileUploadForm extends Form {
		
		public function __construct($name = null) {
			parent::__construct($name);
			
			$this->setAttribute('method','post');
        	$this->setAttribute('enctype','multipart/form-data');
        	//$this->setAttribute('onsubmit', 'return check();');
			
			$this->add(array(
				'name' 			=> 'marketPriceFile',
				'attributes' 	=> array(
					'type'  	=> 'file',
					'id' 		=> 'marketPriceFile',
				),
				'options' 	=> array(
					'label' => 'Market Price Upload : ',
				),
			));
			
			$this->add(array(
				'name' 			=> 'submit',
				'attributes' 	=> array(
					'type'  	=> 'submit',
					'value' 	=> 'Upload Now',
					'class' 	=> 'submit',
					'style' => 'float:none;',
					'onclick' => 'return check();'
				),
			));
			
			$this->add(array(
				'name' => 'btnBack',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Back',
					'class' => 'submit',
					'style' => 'float:none;',
					'onclick' => 'if(confirm("Do you want to go back home page now?")){ return true; } else { return false; }'
				),
			));
			
			$this->add(array(
				'name' => 'btnLogout',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Logout',
					'class' => 'submit',
					'style' => 'float:none;',
					'onclick' => 'if(confirm("Are you sure you want to logout?")){ return true; } else { return false; }'
				),
			));
			//$this->addElements();
			//$this->addInputFilter();
		}
		
		public function addElements() {
			// File Input
			$file = new Element\File('marketPriceFile');
			$file->setAttribute('id', 'marketPriceFile');
			$this->add($file);
		}
	
		public function addInputFilter() {
			$inputFilter = new InputFilter\InputFilter();
	
			// File Input
			$fileInput = new InputFilter\FileInput('marketPriceFile');
			$fileInput->setRequired(true);
			
			$fileInput->getValidatorChain()
				->attachByName('filesize',      array('max' => 10485760))
				->attachByName('filemimetype',  array('mimeType' => 'text/rtf,image/jpeg,text/plain'));
			
			$inputFilter->add($fileInput);
	
			$this->setInputFilter($inputFilter);
		}
		
	}
?>	