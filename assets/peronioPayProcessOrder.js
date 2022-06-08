jQuery(document).ready(function ($) {
  let secret_code = "";

  let account;

  let activeMetamask = true;

  let contractMethods = null;

  let abiContractGateway = [
    {
      inputs: [
        {internalType: "address", name: "currencyAddress", type: "address"},
        {internalType: "address", name: "owner_", type: "address"},
      ],
      stateMutability: "nonpayable",
      type: "constructor",
    },
    {
      anonymous: false,
      inputs: [
        {indexed: false, internalType: "string", name: "id", type: "string"},
        {
          indexed: true,
          internalType: "address",
          name: "payer",
          type: "address",
        },
        {
          indexed: false,
          internalType: "uint256",
          name: "amount",
          type: "uint256",
        },
        {
          indexed: false,
          internalType: "uint256",
          name: "date",
          type: "uint256",
        },
      ],
      name: "Payment",
      type: "event",
    },
    {
      inputs: [],
      name: "currency",
      outputs: [{internalType: "contract IERC20", name: "", type: "address"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [{internalType: "string", name: "", type: "string"}],
      name: "invoices",
      outputs: [
        {internalType: "string", name: "id", type: "string"},
        {internalType: "address", name: "payer", type: "address"},
        {internalType: "uint256", name: "amount", type: "uint256"},
        {internalType: "uint256", name: "date", type: "uint256"},
      ],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [],
      name: "owner",
      outputs: [{internalType: "address", name: "", type: "address"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [
        {internalType: "string", name: "id", type: "string"},
        {internalType: "uint256", name: "amount", type: "uint256"},
      ],
      name: "payInvoice",
      outputs: [],
      stateMutability: "nonpayable",
      type: "function",
    },
  ];

  let abiContractToken = [
    {inputs: [], stateMutability: "nonpayable", type: "constructor"},
    {
      anonymous: false,
      inputs: [
        {
          indexed: true,
          internalType: "address",
          name: "owner",
          type: "address",
        },
        {
          indexed: true,
          internalType: "address",
          name: "spender",
          type: "address",
        },
        {
          indexed: false,
          internalType: "uint256",
          name: "value",
          type: "uint256",
        },
      ],
      name: "Approval",
      type: "event",
    },
    {
      anonymous: false,
      inputs: [
        {indexed: true, internalType: "address", name: "from", type: "address"},
        {indexed: true, internalType: "address", name: "to", type: "address"},
        {
          indexed: false,
          internalType: "uint256",
          name: "value",
          type: "uint256",
        },
      ],
      name: "Transfer",
      type: "event",
    },
    {
      inputs: [
        {internalType: "address", name: "owner", type: "address"},
        {internalType: "address", name: "spender", type: "address"},
      ],
      name: "allowance",
      outputs: [{internalType: "uint256", name: "", type: "uint256"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [
        {internalType: "address", name: "spender", type: "address"},
        {internalType: "uint256", name: "amount", type: "uint256"},
      ],
      name: "approve",
      outputs: [{internalType: "bool", name: "", type: "bool"}],
      stateMutability: "nonpayable",
      type: "function",
    },
    {
      inputs: [{internalType: "address", name: "account", type: "address"}],
      name: "balanceOf",
      outputs: [{internalType: "uint256", name: "", type: "uint256"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [],
      name: "decimals",
      outputs: [{internalType: "uint8", name: "", type: "uint8"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [
        {internalType: "address", name: "spender", type: "address"},
        {internalType: "uint256", name: "subtractedValue", type: "uint256"},
      ],
      name: "decreaseAllowance",
      outputs: [{internalType: "bool", name: "", type: "bool"}],
      stateMutability: "nonpayable",
      type: "function",
    },
    {
      inputs: [
        {internalType: "address", name: "spender", type: "address"},
        {internalType: "uint256", name: "addedValue", type: "uint256"},
      ],
      name: "increaseAllowance",
      outputs: [{internalType: "bool", name: "", type: "bool"}],
      stateMutability: "nonpayable",
      type: "function",
    },
    {
      inputs: [],
      name: "name",
      outputs: [{internalType: "string", name: "", type: "string"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [],
      name: "symbol",
      outputs: [{internalType: "string", name: "", type: "string"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [],
      name: "totalSupply",
      outputs: [{internalType: "uint256", name: "", type: "uint256"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [
        {internalType: "address", name: "to", type: "address"},
        {internalType: "uint256", name: "amount", type: "uint256"},
      ],
      name: "transfer",
      outputs: [{internalType: "bool", name: "", type: "bool"}],
      stateMutability: "nonpayable",
      type: "function",
    },
    {
      inputs: [
        {internalType: "address", name: "from", type: "address"},
        {internalType: "address", name: "to", type: "address"},
        {internalType: "uint256", name: "amount", type: "uint256"},
      ],
      name: "transferFrom",
      outputs: [{internalType: "bool", name: "", type: "bool"}],
      stateMutability: "nonpayable",
      type: "function",
    },
  ];

  /*   const contract_address_gateway = "0x2cAD66fd76145bb4906F886D84F7094bC60F1Fc8";
  const contract_address_token = "0x30c60b8bEc6E8A6FCD72f2D1F90849b6018EDd0b"; */

  const contract_address_gateway = extradata.peronio_payment_address;
  const contract_address_token = extradata.peronio_token_address;

  //Load web3
  window.addEventListener("load", function () {
    if (typeof web3 !== "undefined") {
      window.web3 = new Web3(web3.currentProvider);
    } else {
      $("#metamaskMessage").show();

      activeMetamask = false;
    }

    try {
      //Get secret code
      $.ajax({
        type: "POST",
        url: extradata.ajax,
        data: {
          action: "secret_code",
          nonce: extradata.nonce,
          order_id: extradata.id,
        },
        success: function (response) {
          secret_code = JSON.parse(response).secret_code;
          if (extradata.payment_status == "pending") {
            FirstconfirmPayment();
          } else {
            $("#containerLoading2").hide();
            switch (extradata.payment_status) {
              case "cancelled":
                return $("#buttonCancel").show();
              case "completed":
                return $("#buttonSucess").show();

              case "pending":
                return $("#buttonPay").show();
                break;

              default:
                break;
            }
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          console.log("Status: " + textStatus + "Error: " + errorThrown);
        },
      });
    } catch (error) {
      console.log(error);
    }
  });

  //Open modal
  $("#buttonPay").click(function () {
    $("#modalUpdateWallet").modal("show");
  });

  //Connect with metamask
  $("#metamaskButton").click(async function () {
    if (!activeMetamask) return;

    //Set Loading
    $("#containerLoading").show();
    $("#connectScreen").hide();

    //Chain id
    await changeNetwork();

    //Get contracts methods
    await window.ethereum.request({method: "eth_requestAccounts"});

    const options = {
      clientConfig: {
        // Useful to keep a connection alive
        keepalive: true,
        keepaliveInterval: 60000, // ms
      },

      // Enable auto reconnection
      reconnect: {
        auto: true,
        delay: 10000, // ms
        maxAttempts: Infinity,
        onTimeout: true,
      },
    };

    const webSocket3 = new Web3(
      new Web3.providers.WebsocketProvider(
        "wss://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mumbai/ws",
        options
      )
    );

    let contractToken = new window.web3.eth.Contract(
      abiContractToken,
      contract_address_token
    );
    let contractGateway = new window.web3.eth.Contract(
      abiContractGateway,
      contract_address_gateway
    );

    let contractGatewayWebsocket = new webSocket3.eth.Contract(
      abiContractGateway,
      contract_address_gateway
    );

    contractMethods = {contractToken, contractGateway};

    //Get wallet balance
    tempAccount = await window.web3.eth.getAccounts();
    account = tempAccount[0];

    $("#wallet").text(tempAccount[0]);

    let balance = await contractToken.methods
      .balanceOf(tempAccount[0])
      .call({from: tempAccount[0]});

    //Validate wallet allowance
    let allowance = await contractToken.methods
      .allowance(tempAccount[0], contract_address_gateway)
      .call({from: tempAccount[0]});

    if (allowance.toString() === parseInt(extradata.total) + "0".repeat(18))
      $("#btnPay").show();
    else $("#btnApprove").show();

    //Validate enough tokens to use
    $("#containerLoading").hide();
    if (balance < parseInt(extradata.total)) $("#noFunds").show();
    else $("#payScreen").show();

    //connect to Event
    contractGatewayWebsocket.events
      .Payment()
      .on("data", (event) => confirmPayment(event))
      .on("error", (err) => console.log(err));
  });

  //Connect with wallet connect
  $("#walletConnectButton").click(async function () {
    var provider = new WalletConnectProvider.default({
      rpc: {
        80001:
          "https://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mumbai",
      },
    });

    provider
      .enable()
      .then(async function (res) {
        //Get contracts methods
        //Set Loading
        $("#containerLoading").show();
        $("#connectScreen").hide();

        const options = {
          clientConfig: {
            // Useful to keep a connection alive
            keepalive: true,
            keepaliveInterval: 60000, // ms
          },

          // Enable auto reconnection
          reconnect: {
            auto: true,
            delay: 10000, // ms
            maxAttempts: Infinity,
            onTimeout: true,
          },
        };

        const webSocket3 = new Web3(
          new Web3.providers.WebsocketProvider(
            "wss://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mumbai/ws",
            options
          )
        );

        window.web3 = new Web3(provider);

        let contractToken = new window.web3.eth.Contract(
          abiContractToken,
          contract_address_token
        );
        let contractGateway = new window.web3.eth.Contract(
          abiContractGateway,
          contract_address_gateway
        );

        let contractGatewayWebsocket = new webSocket3.eth.Contract(
          abiContractGateway,
          contract_address_gateway
        );

        contractMethods = {contractToken, contractGateway};

        //Get wallet balance
        account = res[0];

        $("#wallet").text(account);

        let balance = await contractToken.methods
          .balanceOf(account)
          .call({from: account});

        //Validate wallet allowance
        let allowance = await contractToken.methods
          .allowance(account, contract_address_gateway)
          .call({from: account});

        if (allowance.toString() === parseInt(extradata.total) + "0".repeat(18))
          $("#btnPay").show();
        else $("#btnApprove").show();

        //Validate enough tokens to use
        $("#containerLoading").hide();
        /*  if (balance < parseInt(extradata.total)) $("#noFunds").show();
         else  */ $("#payScreen").show();

        //connect to Event
        contractGatewayWebsocket.events
          .Payment()
          .on("data", (event) => confirmPayment(event))
          .on("error", (err) => console.log(err));
      })
      .catch((err) => console.log(err));
  });

  //Reject Payment
  $("#payCancel").click(async function () {
    $("#modalUpdateWallet").modal("hide");
    $("#connectScreen").show();
    $("#payScreen").hide();
  });

  //Send Payment
  $("#paySend").click(async function () {
    $("#containerLoading").show();
    $("#payScreen").hide();
    $("#error-tag").hide();

    await contractMethods.contractGateway.methods
      .payInvoice(extradata.nonce, parseInt(extradata.total) + "0".repeat(18))
      .send({
        from: account,
      })
      .catch((err) => {
        $("#error-tag").show();
        $("#containerLoading").hide();
        $("#payScreen").show();
      });
  });

  //Approve Payment
  $("#payApprove").click(async function () {
    $("#containerLoading").show();
    $("#payScreen").hide();
    $("#error-tag").hide();

    await contractMethods.contractToken.methods
      .approve(
        contract_address_gateway,
        parseInt(extradata.total) + "0".repeat(18)
      )
      .send({from: account})
      .then((result) => {
        /*  $("#containerLoading").hide();
        $("#payScreen").show();

        $("#btnPay").show();
        $("#btnApprove").hide(); */
      })
      .catch((err) => {
        $("#error-tag").show();
        $("#containerLoading").hide();
        $("#payScreen").show();
      });

    let intervalId = setInterval(async () => {
      let allowance = await contractMethods.contractToken.methods
        .allowance(account, contract_address_gateway)
        .call({from: account})
        .then((result) => {
          if (
            result.toString() ===
            parseInt(extradata.total) + "0".repeat(18)
          ) {
            clearInterval(intervalId);
            $("#containerLoading").hide();
            $("#payScreen").show();

            $("#btnPay").show();
            $("#btnApprove").hide();
          }
        });
    }, 3000);
  });

  function confirmPayment(event) {
    if (event.returnValues.id === extradata.nonce) {
      $.ajax({
        type: "POST",
        url: extradata.ajax,
        data: {
          action: "verifyPayment",
          nonce: extradata.nonce,
          order_id: extradata.id,
          total: parseInt(extradata.total) + "0".repeat(18),
          secret_code: secret_code,
          transaction_id: event.transactionHash,
        },
        success: function (response) {
          location.reload();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          location.reload();
        },
      });
    }
  }

  function FirstconfirmPayment() {
    $.ajax({
      type: "POST",
      url: extradata.ajax,
      data: {
        action: "verifyPayment",
        nonce: extradata.nonce,
        order_id: extradata.id,
        total: parseInt(extradata.total) + "0".repeat(18),
        secret_code: secret_code,
        transaction_id: "---",
      },
      success: function (response) {
        if (
          extradata.peronio_token_address === "" ||
          extradata.peronio_payment_address === ""
        ) {
          $("#buttonDisable").show();
          $("#containerLoading2").hide();
        } else if (response.toString().trim() == "false0") {
          $("#containerLoading2").hide();
          switch (extradata.payment_status) {
            case "cancelled":
              return $("#buttonCancel").show();
            case "completed":
              return $("#buttonSucess").show();

            case "pending":
              return $("#buttonPay").show();
              break;

            default:
              break;
          }
        } else {
          /*  location.reload(); */
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        location.reload();
      },
    });
  }

  async function changeNetwork() {
    await window.ethereum.request({
      method: "wallet_addEthereumChain",
      params: [
        {
          chainId: `0x${Number(80001).toString(16)}`,
          chainName: "Matic(Polygon) Mumbai Testnet",
          nativeCurrency: {name: "tMATIC", symbol: "tMATIC", decimals: 18},
          rpcUrls: [
            "https://speedy-nodes-nyc.moralis.io/9aadb4a1729324fc6f104286/polygon/mumbai",
          ],
          blockExplorerUrls: ["https://mumbai.polygonscan.com/"],
        },
      ],
    });
  }
});
