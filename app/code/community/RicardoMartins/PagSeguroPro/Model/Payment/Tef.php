<?php
class RicardoMartins_PagSeguroPro_Model_Payment_Tef extends RicardoMartins_PagSeguro_Model_Abstract
{
    protected $_code = 'pagseguropro_tef';
    protected $_formBlockType = 'ricardomartins_pagseguropro/form_tef';
    protected $_infoBlockType = 'ricardomartins_pagseguropro/form_info_tef';
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
        $info->setAdditionalInformation('tef_bank', $data->getPagseguroproTefBank());
        if(Mage::helper('ricardomartins_pagseguro')->isCpfVisible()) {
            $info->setAdditionalInformation($this->getCode() . '_cpf', $data->getData($this->getCode() . '_cpf'));
        }

        return $this;
    }


    public function order(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $helper = Mage::helper('ricardomartins_pagseguropro/internal');

        //montaremos os dados a ser enviados via POST pra api em $params
        $params = $helper->getTefApiCallParams($order, $payment);

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
        if (isset($xmlRetorno->error->message)) {
            $helper = Mage::helper('ricardomartins_pagseguro');
            $msg = 'Um erro ocorreu: ' . $helper->__((string)$xmlRetorno->error->message);
            if ($xmlRetorno->error->code == 53085) {
                $msg .= ' Selecione outro banco ou outra forma de pagamento.';
            }
            Mage::throwException($msg);
        }

        $payment->setSkipOrderProcessing(true);

        if(isset($xmlRetorno->code)){
            $payment->setAdditionalInformation(array('transaction_id'=>(string)$xmlRetorno->code));
        }

        if(isset($xmlRetorno->paymentMethod->type) && (int)$xmlRetorno->paymentMethod->type == 3)
        {
           $payment->setAdditionalInformation('tefUrl', (string)$xmlRetorno->paymentLink);
        }


        return $this;

    }

}