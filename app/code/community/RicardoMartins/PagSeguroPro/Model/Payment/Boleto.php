<?php
class RicardoMartins_PagSeguroPro_Model_Payment_Boleto extends RicardoMartins_PagSeguro_Model_Abstract
{
    protected $_code = 'pagseguropro_boleto';
    protected $_formBlockType = 'ricardomartins_pagseguropro/form_boleto';
    protected $_infoBlockType = 'ricardomartins_pagseguropro/form_info_boleto';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = false;
    protected $_canRefund = false;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;


    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('sender_hash', $data->getSenderHash());
        if(Mage::helper('ricardomartins_pagseguro')->isCpfVisible()){
            $info->setAdditionalInformation($this->getCode() . '_cpf', $data->getData($this->getCode().'_cpf'));
        }

        return $this;
    }


    public function order(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        $helper = Mage::helper('ricardomartins_pagseguropro/internal');

        //montaremos os dados a ser enviados via POST pra api em $params
        $params = $helper->getBoletoApiCallParams($order, $payment);

        //chamamos a API
        $xmlRetorno = $this->callApi($params,$payment);
        $xmlRetorno = $helper->validate($xmlRetorno);
//        $this->proccessNotificatonResult($xmlRetorno);

        if(isset($xmlRetorno->errors)){
            $errMsg = array();
            foreach($xmlRetorno->errors as $error){
                $errMsg[] = (string)$error->message . '(' . $error->code . ')';
            }
            Mage::throwException('Um ou mais erros ocorreram no seu pagamento.' . PHP_EOL . implode(PHP_EOL,$errMsg));
        }
        $payment->setSkipOrderProcessing(true);

        if(isset($xmlRetorno->code)){
            $payment->setAdditionalInformation(array('transaction_id'=>(string)$xmlRetorno->code));
        }

        if(isset($xmlRetorno->paymentMethod->type) && (int)$xmlRetorno->paymentMethod->type == 2)
        {
           $payment->setAdditionalInformation('boletoUrl', (string)$xmlRetorno->paymentLink);
        }


        return $this;

    }

}