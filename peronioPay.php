
<?php

/**
 * Plugin Name: Peronio Payment Gateway
 * Plugin URI: https://peronio.ar/
 * Author Name: Duwan Pena
 * Author URI: https://peronio.ar/
 * Description: This plugin allows payments with web3 and PE Token
 * Version: 0.1.0
 * License: 0.1.0
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: peronio-pay
*/ 

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

define('WC_PE_PAY_VERSION', '1.0.0');
define('WC_PE_PAY_FILE', __FILE__);
define('WC_PE_PAY_PATH', plugin_dir_path(WC_PE_PAY_FILE));
define('WC_PE_PAY_URL', plugin_dir_url(WC_PE_PAY_FILE)); 

//Development
/*  define("WC_PE_RPC", "https://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mumbai");
define("WC_PE_RPC_WEBSOCKET", "wss://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mumbai/ws") ;
define("WC_PE_RPC_PRODUCTION", "false") ;  */

//Production
 define("WC_PE_RPC", "https://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mainnet");
define("WC_PE_RPC_WEBSOCKET", "wss://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mainnet/ws") ;
define("WC_PE_RPC_PRODUCTION", "true") ; 

add_action( 'plugins_loaded', 'peronio_payment_init', 11 );
register_activation_hook(__FILE__,'registerPeronioGeneralSettings');
require __DIR__ . '/vendor/autoload.php';
use Ethereum\Ethereum;
use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
function peronio_payment_init() {
    if( class_exists( 'WC_Payment_Gateway' ) ) {
        class WC_peronio_pay_Gateway extends WC_Payment_Gateway {
            public function __construct() {
                $this->id   = 'peronio_pay';
                $this->has_fields = false;
                $this->method_title = __( 'Peronio Payment', 'peronio_pay');
                $this->method_description = __( 'Pay your order with PE Token', 'peronio_pay');
                $this->title = $this->get_option( 'title' );
                $this->description = $this->get_option( 'description' );
                $this->instructions = $this->get_option( 'instructions', $this->description );
                $this->init_form_fields();
                $this->init_settings();
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action('woocommerce_thankyou_' . $this->id, array($this, 'thank_you_page'));    
            }

            public function init_form_fields() {
                $this->form_fields = apply_filters( 'woo_peronio_pay_fields', array(
                    'enabled' => array(
                        'title' => __( 'Enable/Disable', 'peronio_pay'),
                        'type' => 'checkbox',
                        'label' => __( 'Enable or Disable Peronio Payments', 'peronio_pay'),
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => __( 'Peronio Pay ', 'peronio_pay'),
                        'type' => 'text',
                        'default' => __( 'Peronio Pay ', 'peronio_pay'),
                        'desc_tip' => true,
                        'description' => __( 'Add a new title for the Peronio Payment Gateway that customers will see when they are in the checkout page.', 'peronio_pay')
                    ),
                    'description' => array(
                        'title' => __( 'Peronio Payment Gateway Description', 'peronio_pay'),
                        'type' => 'textarea',
                        'default' => __( 'Please remit your payment to the shop to allow for the delivery to be made', 'peronio_pay'),
                        'desc_tip' => true,
                        'description' => __( 'Add a new title for the Peronio Payment Gateway that customers will see when they are in the checkout page.', 'peronio_pay')
                    ),
                    'instructions' => array(
                        'title' => __( 'Instructions', 'peronio_pay'),
                        'type' => 'textarea',
                        'default' => __( 'Default instructions', 'peronio_pay'),
                        'desc_tip' => true,
                        'description' => __( 'Instructions that will be added to the thank you page and order email', 'peronio_pay')
                    ),
                ));
            }

            public function process_payment( $order_id ) {
                $order = wc_get_order( $order_id );
                $order->update_status( 'pending-process',  __( 'Awaiting Peronio Payment', 'peronio_pay') );
                $order->reduce_order_stock();
                WC()->cart->empty_cart();
                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order ),
                );
            }

            public function thank_you_page($order_id){
                 require_once WC_PE_PAY_PATH . '/peronioPayProcessOrder.php'; 
            }


          

        }
    }
}

function registerPeronioGeneralSettings(){
    if(!get_option('peronio_gateway_address'))
        add_option('peronio_gateway_address', '');
    
    if(!get_option('peronio_token_address'))
        add_option('peronio_token_address', '0xc2768beF7a6BB57F0FfA169a9ED4017c09696FF1');
    
    if(!get_option('peronio_payment_address'))
        add_option('peronio_payment_address', '');

    if(!get_option('peronio_owner_address'))
        add_option('peronio_owner_address', '');

        
    if(!get_option('peronio_decimals_token'))
        add_option('peronio_decimals_token', '6');
    
};
   
function CreateMenuPeronio(){
    add_menu_page(
        "Peronio Payment ", 
        "Peronio payment ", 
        "manage_options", 
        plugin_dir_path(__FILE__)."peronioMenu.php", 
        null, 
        "");
};

function UpdateForm () {
  if(is_admin()){
    update_option('peronio_gateway_address', $_POST["peronio_gateway_address"]);
    update_option('peronio_token_address', $_POST["peronio_token_address"]);
    update_option('peronio_payment_address', $_POST["peronio_payment_address"]);
    update_option('peronio_owner_address', $_POST["peronio_owner_address"]);
    update_option('peronio_decimals_token', $_POST["peronio_decimals_token"]);
  }
  return false;

}

 function VerifyPayment(){
     $nonce = $_POST["nonce"];
     $order_id = $_POST["order_id"];
     $secret_code = !empty($_REQUEST['secret_code']) ? $_REQUEST['secret_code'] : "";
     $verify_secretCode = get_post_meta($order_id, 'peronio_secret_code', true);
     if ($secret_code != $verify_secretCode)  die("*Unauthorized access*");

    try {
  
 
         $web3 = new Web3(new HttpProvider(new HttpRequestManager(WC_PE_RPC, 1000)));
         $contractAddress = get_option('peronio_payment_address');
         $abiContract = json_decode('[{"inputs":[{"internalType":"address","name":"currencyAddress","type":"address"},{"internalType":"address","name":"owner_","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"string","name":"id","type":"string"},{"indexed":true,"internalType":"address","name":"payer","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"date","type":"uint256"}],"name":"Payment","type":"event"},{"inputs":[],"name":"currency","outputs":[{"internalType":"contract IERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"","type":"string"}],"name":"invoices","outputs":[{"internalType":"string","name":"id","type":"string"},{"internalType":"address","name":"payer","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"},{"internalType":"uint256","name":"date","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"id","type":"string"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"payInvoice","outputs":[],"stateMutability":"nonpayable","type":"function"}]');
         $contract = new Contract(new HttpProvider(new HttpRequestManager(WC_PE_RPC, 1000)), $abiContract);

         $contract->at($contractAddress)->call('invoices', $nonce ,function ($err, $data) {
             
               if ($err !== null) {
                 throw new Exception($err->getMessage());
               }
               $nonce = $_POST["nonce"];
               $total = $_POST["total"];
               $order_id = $_POST["order_id"];
               $transaction_id = $_POST["transaction_id"];

               $nonceContract = $data["id"];
               $amountContract = $data["amount"];

               if($nonce == $nonceContract && $total == $amountContract){

                $order = new WC_Order($order_id);
               $order->add_meta_data('TransactionId', $transaction_id);
               $order->add_meta_data('orderId', $order_id);
               $order->add_meta_data('nonce', $nonce);
               $transection = 'Payment Received - Transaction ID:'. $transaction_id;                
               $order->add_order_note($transection);
               $order->update_status("completed");
               echo("true");
               }else echo("false");

              });

  } catch (\Throwable $th) {
    echo( "$th");
  }
      
};

 function GetSecretCode(){

    global $woocommerce;  
    
    $order_id = sanitize_text_field($_REQUEST['order_id']);
    $order = new WC_Order($order_id);
    $nonce = !empty($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : "";
    

    $secretCode = hash_hmac('ripemd160', $orderId, time());
    update_post_meta($order_id, 'peronio_secret_code', $secretCode);

    $data = [
        'secret_code' =>$secretCode,                    
    ];
    echo json_encode($data);

                die(); 
               
};

add_filter( 'woocommerce_payment_gateways', 'add_to_woo_peronio_payment_gateway'); 
add_action("admint_init", "registerPeronioGeneralSettings");
add_action("admin_menu", "CreateMenuPeronio"); 

add_action('wp_ajax_nopriv_secret_code','GetSecretCode');
add_action('wp_ajax_secret_code','GetSecretCode');
add_action('wp_ajax_verifyPayment','VerifyPayment'); 
add_action('wp_ajax_nopriv_verifyPayment','VerifyPayment'); 
add_action('wp_ajax_updateForm','UpdateForm'); 


function add_to_woo_peronio_payment_gateway( $gateways ) {
    $gateways[] = 'WC_peronio_pay_Gateway';
    return $gateways;
}
