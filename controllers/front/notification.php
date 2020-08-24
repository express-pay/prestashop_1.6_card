<?php

require_once __DIR__ . "/ExpressPay.php";
/**
 * @since 1.0.0
 */
class expresspay_cardnotificationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $config = json_decode(Configuration::get("EXPRESSPAY_CARD_CONFIG"));

        $notification = null;
        try {
            $notification = new Notification($config->use_digital_sign_receive, $config->receive_secret_word);
        }
        catch (Exception $exception)
        {
            die($exception->getMessage());
        }

        $history = new OrderHistory();

        $history->id_order = $notification->accountNo;

        switch($notification->cmdtype){
            case 1:
                $history->changeIdOrderState(2, $history->id_order);//Изменим статус заказа на "Оплачен"
                header("HTTP/1.0 200 OK");
                die('Order payment success');
                break;
            case 2:
                $history->changeIdOrderState(2,$history->id_order);//Изменим статус заказа на "Отменен"
                header("HTTP/1.0 200 OK");
                die('Order canceled');
                break;
            default:
                header("HTTP/1.0 200 OK");
                die();
        }
    }
}
