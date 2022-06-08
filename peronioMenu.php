<?php 

wp_enqueue_style('peronio_pay_css', WC_PE_PAY_URL . 'assets/peronioPayProcessOrder.css', array(), WC_PE_PAY_VERSION, null, 'all');

?>

       <script src=https://unpkg.com/@walletconnect/web3-provider@1.7.1/dist/umd/index.min.js></script>
        <script src=https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js></script>
        <script src=https://cdn.jsdelivr.net/npm/ethjs@latest/dist/ethjs.min.js></script>
        <script src=https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js></script>
  <link href=https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css rel=stylesheet integrity=sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor crossorigin=anonymous>
  <script src=https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js integrity=sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2 crossorigin=anonymous></script>
       
    
  <div id="form-wallets" class="m-5 container-form border" >

   <h4> Peronio Payment Settings </h4>

  
   <div  class="text-left"> 
       <label class="col-12 container-label"> Gateway Factory Address: </label>
       <input type="text" id="input1" class="container-input" value="<?php  echo get_option("peronio_gateway_address") ?>" /> 
      </div>
      <div class="text-left"> 
        <label class="col-12 container-label"> Token ERC Address: </label>
        <input type="text" id="input2" class="container-input" value="<?php  echo get_option("peronio_token_address") ?>" /> 
      </div>
      <div class="text-left"> 
        <label class="col-12 container-label"> Payment Gateway Address: </label>
        <input type="text" id="input3" class="container-input" value="<?php  echo get_option("peronio_payment_address") ?>" /> 
      </div>
      <div class="text-left"> 
        <label class="col-12 container-label"> Owner Address: </label>
        <input type="text" id="input4" class="container-input" value="<?php  echo get_option("peronio_owner_address") ?>" /> 
      </div>
      <button id="button-change-form" class="container-button-form"> Update Contracts with form </button>
      <button id="button-change" class="container-button"> generate new payment gateway contract </button>
      <button id="button-metamask" class="container-button"> Connect Metamask </button>
      <p id="text-metamask" class="container-button"> Please install metamask to update the contract</p>
      
    </div>
    <div id='containerLoading2' class='containerLoading2'> 

               <svg 
               id='loading-spinner' 
               width='80' 
               height='80' 
               viewBox='0 0 80 80' 
               fill='none' 
               xmlns='http://www.w3.org/2000/svg'>
	             
                 <circle id='loading-circle-large' cx='40' cy='40' r='36' stroke='#005CB9' stroke-width='8' />
               </svg>
               <p class=loadingText > Cargando... </p>
                   
        </div>

    

<?php

wp_enqueue_script('peronio_pay2_js', WC_PE_PAY_URL . 'assets/peronioMenu.js', array('jquery'), WC_PE_PAY_VERSION, true);
  wp_localize_script('peronio_pay2_js', "extradata",
    array(
      'ajax' => home_url('/wp-admin/admin-ajax.php'),
      'peronio_gateway_address' => get_option('peronio_gateway_address'),
      'peronio_token_address' => get_option('peronio_token_address'),
      'peronio_payment_address' => get_option('peronio_payment_address'),
      'peronio_owner_address' => get_option('peronio_owner_address'),
       'rpc' => WC_PE_RPC,
       'rpc_ws' => WC_PE_RPC_WEBSOCKET,
       'rpc_enviroment' => WC_PE_RPC_PRODUCTION,
      )
  );


