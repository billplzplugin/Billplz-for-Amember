<?php
/**
 * @table paysystems
 * @id billplz
 * @title Billplz
 * @visible_link http://www.billplz.com/
 * @recurring none
 * @logo_url billplz.png
 */
 
 // Method to create a Bill
 
 if(!function_exists('DapatkanLink')){
	 function DapatkanLink($host, $api_key, $billplz_data){
		$process = curl_init($host . "bills/");
		curl_setopt($process, CURLOPT_HEADER, 0);
		curl_setopt($process, CURLOPT_USERPWD, $api_key . ":");
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($billplz_data));
		$return = curl_exec($process);
		curl_close($process);
		$arr = json_decode($return, true);
		if (isset($arr['error'])){
			unset($billplz_data['mobile']);
			$process = curl_init($host . "bills/");
			curl_setopt($process, CURLOPT_HEADER, 0);
			curl_setopt($process, CURLOPT_USERPWD, $api_key . ":");
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($billplz_data));
			$return = curl_exec($process);
			curl_close($process);
			$arr = json_decode($return, true);
			if (isset($arr['error'])){
				$arr = array("url"=>"https://fb.com/billplzplugin");
				return $arr;
			}
			else {
				return $arr;
			}
		}
		else {
			return $arr;
		}
	 }
 }
 if(!function_exists('DapatkanInfo')){
	 function DapatkanInfo($host, $api_key, $id){
		$process = curl_init($host . 'bills/'. $id);
		curl_setopt($process, CURLOPT_HEADER, 0);
		curl_setopt($process, CURLOPT_USERPWD, $api_key . ":");
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($process);
		curl_close($process);
		$arra = json_decode($return, true);
		return $arra;
	 }
 }
 
 if(!function_exists('DapatkanHost')){
	 function DapatkanHost($status){
		 if ($status=='1'){
			 return 'https://www.billplz.com/api/v3/';
		 }
		 elseif ($status=='2'){
			 return 'https://billplz-staging.herokuapp.com/api/v3/';
		 }
	 }
 }
 
 // End of Method to create a Bill
 
class Am_Paysystem_Billplz extends Am_Paysystem_Abstract
{
    const PLUGIN_STATUS = self::STATUS_BETA;
   
    protected $_canResendPostback = true;
    protected $defaultTitle = 'Billplz';
    protected $defaultDescription = 'Online Payment (Maybank2u, CIMB Clicks, Bank Islam, UOB, RHB Bank, HLB, FPX)';
    
    public function getSupportedCurrencies()
    {
        return array('RM', 'MYR');
    }
    public function getRecurringType()
    {
        return self::REPORTS_NOT_RECURRING;
    }
    public function isConfigured()
    {
        return strlen($this->getConfig('merchant_id'));
    }
    public function _initSetupForm(Am_Form_Setup $form)
    {
        $form->addText('merchant_id', array('size' => 20))
            ->setLabel("API Key")
            ->addRule('required');
        $form->addText('verify_key', array('size' => 20))
            ->setLabel("Collection ID")
            ->addRule('required');
		$form->addText('productionsandbox', array('size' => 2))
            ->setLabel("Production(1)/Staging(2) Mode")
            ->addRule('required');
			
    }
    
    public function _process(Invoice $invoice, Am_Mvc_Request $request, Am_Paysystem_Result $result)
    {
        $Payment_Method = '';
        $api_key 	= $this->getConfig('merchant_id')=='' ? '73eb57f0-7d4e-42b9-a544-aeac6e4b0f81' : $this->getConfig('merchant_id');
		$collection_id = $this->getConfig('verify_key')=='' ? 'inbmmepb' : $this->getConfig('verify_key');
		$host = DapatkanHost($this->getConfig('productionsandbox'));
		$deliver = false;
		
		//number intelligence
        $custTel    = $invoice->getPhone();
        $custTel2   = substr($invoice->getPhone(), 0, 1);
        if ($custTel2 == '+') {
			$custTel3 = substr($invoice->getPhone(), 1, 1);
            if ($custTel3 != '6'){
				$custTel = "+6" . $invoice->getPhone();
			}
		} 
		else if ($custTel2 == '6') {
        } 
		else {
			if ($custTel != ''){
				$custTel = "+6" . $invoice->getPhone();
			}
        }
		//number intelligence
		//$a->cur         = $invoice->getCurrency();
		//$invoice->public_id
		$billplz_data = array(
                'amount' => $invoice->first_total * 100,
                'name' => utf8_encode($invoice->getName()),
                'email' => $invoice->getEmail(),
                'collection_id' => $collection_id,
                'mobile' => $custTel,
                'reference_1_label' => "ID",
                'reference_1' => $invoice->public_id,
                'deliver' => $deliver,
                'description' => substr(utf8_encode($invoice->getLineDescription()), 0, 199),
                'redirect_url' => $this->getPluginUrl('thanks'),
                'callback_url' => $this->getPluginUrl('ipn')
		);
		
		$arr = DapatkanLink($host, $api_key, $billplz_data);
		$url = $arr['url'];
        
		$a = new Am_Paysystem_Action_Redirect($url);
		
        $a->filterEmpty();
        $result->setAction($a);
    }

    public function createTransaction(Am_Mvc_Request $request, Am_Mvc_Response $response, 
        array $invokeArgs)
    {
        return new Am_Paysystem_Transaction_Billplz($this, $request, $response, $invokeArgs);
    }
    public function createThanksTransaction(Am_Mvc_Request $request, Am_Mvc_Response $response, 
        array $invokeArgs)
    {
        return new Am_Paysystem_Transaction_Billplz_Thanks($this, $request, $response, $invokeArgs);
    }
    public function getReadme()
    {
        return <<<CUT
                      Billplz plugin installation

 1. Enable "Billplz" payment plugin at aMember CP -> Setup/Configuration -> Plugins
    
 2. Configure plugin: aMember CP -> Setup/Configuration -> Billplz
    
 3. Insert API Key and Collection ID
    
 4. Run a test transaction to ensure everything is working correctly.
 

IMPORTANT! Insert number one "1" without double quotes for Production Mode
IMPORTANT! Insert number two "2" without double quotes for Sandbox Mode

You need to have Staging API Key at https://billplz-staging.herokuapp.com
to use a Sandbox Mode.


------------------------------------------------------------------------------

CUT;
    }
}
if(!class_exists('Am_Paysystem_Transaction_Billplz', false)){
class Am_Paysystem_Transaction_Billplz extends Am_Paysystem_Transaction_Incoming
{
	var $b;
    public function getUniqId()
    {
         return $this->request->get('id');       
    }
    public function getReceiptId()
    {
        return $this->request->get('id');
    }
    public function getAmount()
    {
		return moneyRound($this->b['amount']/100);
    }
    public function findInvoiceId()
    {
		return $this->b['reference_1'];
    }
    public function validateSource()
    {
		$this->b = DapatkanInfo(DapatkanHost($this->getPlugin()->getConfig('productionsandbox')), $this->getPlugin()->getConfig('merchant_id'), $this->request->get('id'));
        return $this->b['paid'];
    }
    public function validateStatus()
    {
		return $this->b['state'] == 'paid';
    }
    public function validateTerms()
    {
        return true;
    }
    public function processValidated()
    {        
        $this->invoice->addPayment($this);
    }
}
}
if(!class_exists('Am_Paysystem_Transaction_Billplz_Thanks', false)) 
{
class Am_Paysystem_Transaction_Billplz_Thanks extends Am_Paysystem_Transaction_Incoming_Thanks
{
	var $b;
    public function findInvoiceId()
    {
		return $this->b['reference_1'];
    }
    public function getUniqId()
    {
		 return htmlspecialchars($_GET['billplz']['id']);
    }
    public function validateStatus()
    {
		return $this->b['state'] == 'paid' AND $this->b['paid'];
    }
    public function validateTerms()
    {
        return true;
    }
    public function validateSource()
    {
		$this->b = DapatkanInfo(DapatkanHost($this->getPlugin()->getConfig('productionsandbox')), $this->getPlugin()->getConfig('merchant_id'), htmlspecialchars($_GET['billplz']['id']));
		return true;
    }
    function process()
    {
        try {
			parent::process();
		}catch(Exception $e){
			$this->getPlugin()->_setInvoice($this->invoice);
			Am_Controller::redirectLocation($this->getPlugin()->getCancelUrl());
		}
    }
}
}
