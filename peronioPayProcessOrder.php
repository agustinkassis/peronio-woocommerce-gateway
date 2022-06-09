<?php

  $order = new WC_Order($order_id);

  $total = $order->get_total();

  $nonce = wp_create_nonce("peronio_pay_$order_id");

  $payment_status = $order->get_status();

  $metadata = $order->get_meta_data();

  $user = wp_get_current_user();

  if($user->exists()) {$activeUser = "true";}
  else {$activeUser = "false";}

  $payed = $order->is_paid();


 
  wp_enqueue_style('peronio_pay_css', WC_PE_PAY_URL . 'assets/peronioPayProcessOrder.css', array(), WC_PE_PAY_VERSION, null, 'all');



?>

        <script src=https://unpkg.com/@walletconnect/web3-provider@1.7.1/dist/umd/index.min.js></script>
        <script src=https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js></script>
        <script src=https://cdn.jsdelivr.net/npm/ethjs@latest/dist/ethjs.min.js></script>
        <script src=https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js></script>
  <link href=https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css rel=stylesheet integrity=sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor crossorigin=anonymous>
  <script src=https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js integrity=sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2 crossorigin=anonymous></script>
       
  <div class="mb-5 text-center" >

        <button class=btn-Pay id=buttonPay> 
           CONFIRMAR PAGO
        </button>
        <button class=btn-Success id=buttonSucess> 
           PAGO COMPLETADO
        </button>
        <button class=btn-Cancel id=buttonCancel> 
           PAGO CANCELADO
        </button>
        <button class=btn-Disable id=buttonDisable> 
           PAGO NO DISPONIBLE
        </button>

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

  

       <div class=modal fade id=modalUpdateWallet data-bs-backdrop=static data-bs-keyboard=false tabindex=-1 aria-labelledby=staticBackdropLabel aria-hidden=true>
  <div class='modal-dialog modal-dialog-centered'>
    <div class=modal-content>
      <div class=modal-header>
        <h5 class=card-modal-title id=staticBackdropLabel>Metodo de pago</h5>
        <button type=button class=card-close data-bs-dismiss=modal aria-label=Close>X</button>
      </div>
      
          <div  class=modal-body>

               <div id='containerLoading' class='containerLoading'> 

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

               <div id='connectScreen' >
                <button id=metamaskButton class=card-wallet> <p><img  src="<?php echo esc_url(WC_PE_PAY_URL . 'assets/images/MetaMask.webp') ?>" class=img-wallet  alt=metamask />  Metamask </p>  <p id=metamaskMessage class='notMetamask'>(Extension no encontrada, porfavor install Metamask)</p> </button>
                <button id=walletConnectButton class=card-wallet> <img  src="<?php echo esc_url(WC_PE_PAY_URL . 'assets/images/walletconnect-logo.svg') ?>" class=img-wallet  alt=metamask />   WalletConnect </button>
                 <p class='priceAmountFounds error-wallet' id="error-walletconnect"  > Error al conectarte con WalletConnect, porfavor asegurate de estar en la red de polygon para poder realizar la conexion </p>
              </div>   
               
                <div id='payScreen' class='payScreen'> 
                    <h3> Cartera conectada </h3>
                    <p id="wallet" class='wallet'>  </p>

                    <p class='priceTitle' >Monto a pagar</p>
                    <p class='priceAmount' > <?php echo esc_html($total); ?> PE </p>


                    <div class='containerPayButtons'  id="btnApprove"> 
                    <button id='payApprove' class='paySend'> Aprobar transaccion </button>
                    </div>
                    <div class='containerPayButtons' id="btnPay"> 
                    <button id='paySend' class='paySend'> Confirmar transaccion </button>
                    </div>

                    <p class='error-tag' id='error-tag' >Error mientras se ejecutaba la transaccion</p>

                 </div>
                <div id='noFunds' class='payScreen'> 
                    <h3> Cartera conectada  </h3>
                    <p id="wallet" class='wallet'>  </p>

                    <p class='priceTitle' >Monto a pagar</p>
                    <p class='priceAmount' > <?php echo esc_html($total); ?> PE </p>
                    <p class='priceAmountFounds' > No tienes suficientes fondos para realizar el pago </p>
                 </div>

             
          </div>

         
      

     
    </div>
  </div>
</div>

          
        </div>

<?php

wp_enqueue_script('peronio_pay_js', WC_PE_PAY_URL . 'assets/peronioPayProcessOrder.js', array('jquery'), WC_PE_PAY_VERSION, true);

  wp_localize_script('peronio_pay_js', "extradata",
    array(
         'total' => $total,
        'id' => $order_id,
        'nonce' => $nonce,
         'payment_status' => $payment_status,
         'order' => $order,
         'metadata' => $metadata,
         'activeUser' => $activeUser,
         'ajax' => home_url('/wp-admin/admin-ajax.php'),
      'peronio_token_address' => get_option('peronio_token_address'),
      'peronio_payment_address' => get_option('peronio_payment_address'),
      'peronio_decimals_token' => get_option('peronio_decimals_token'),
       'rpc' => WC_PE_RPC,
       'rpc_ws' => WC_PE_RPC_WEBSOCKET,
       'rpc_enviroment' => WC_PE_RPC_PRODUCTION,
    )
  );


