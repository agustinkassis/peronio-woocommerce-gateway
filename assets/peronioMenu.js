jQuery(document).ready(function ($) {
  let activeMetamask = true;
  let abiContractCreateGateway = [
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
          indexed: false,
          internalType: "address",
          name: "contractAddress",
          type: "address",
        },
        {
          indexed: false,
          internalType: "address",
          name: "currencyAddress",
          type: "address",
        },
        {
          indexed: false,
          internalType: "uint256",
          name: "date",
          type: "uint256",
        },
      ],
      name: "GatewayCreated",
      type: "event",
    },
    {
      inputs: [
        {internalType: "address", name: "currencyAddress", type: "address"},
        {internalType: "address", name: "owner_", type: "address"},
      ],
      name: "createGateway",
      outputs: [
        {internalType: "address", name: "gatewayAddress", type: "address"},
      ],
      stateMutability: "nonpayable",
      type: "function",
    },
    {
      inputs: [],
      name: "gateswaysLength",
      outputs: [{internalType: "uint256", name: "", type: "uint256"}],
      stateMutability: "view",
      type: "function",
    },
    {
      inputs: [{internalType: "uint256", name: "", type: "uint256"}],
      name: "gateways",
      outputs: [
        {internalType: "address", name: "owner", type: "address"},
        {internalType: "address", name: "contractAddress", type: "address"},
        {internalType: "address", name: "currencyAddress", type: "address"},
        {internalType: "uint256", name: "createdAt", type: "uint256"},
      ],
      stateMutability: "view",
      type: "function",
    },
  ];

  /* const contract_address_create_gateway = "0x725f10C9C90B132220Fe65E8819a5Df0FfF7Ef3D"; */
  const contract_address_create_gateway = extradata.peronio_gateway_address;
  //Load web3
  window.addEventListener("load", function () {
    $("#containerLoading2").hide();
    if (typeof web3 !== "undefined") {
      window.web3 = new Web3(web3.currentProvider);
      $("#button-change").show();
    } else {
      $("#text-metamask").show();
      activeMetamask = false;
    }
  });

  async function changeNetwork() {
    if (extradata.rpc_enviroment === "true") {
      await window.ethereum.request({
        method: "wallet_addEthereumChain",
        params: [
          {
            chainId: `0x${Number(137).toString(16)}`,
            chainName: "Matic(Polygon) Mainnet",
            nativeCurrency: {name: "MATIC", symbol: "MATIC", decimals: 18},
            rpcUrls: [extradata.rpc],
            blockExplorerUrls: ["https://www.polygonscan.com"],
          },
        ],
      });
    } else {
      await window.ethereum.request({
        method: "wallet_addEthereumChain",
        params: [
          {
            chainId: `0x${Number(80001).toString(16)}`,
            chainName: "Matic(Polygon) Mumbai Testnet",
            nativeCurrency: {name: "tMATIC", symbol: "tMATIC", decimals: 18},
            rpcUrls: [extradata.rpc],
            blockExplorerUrls: ["https://mumbai.polygonscan.com/"],
          },
        ],
      });
    }
  }

  $("#button-change-form").click(function (e) {
    e.preventDefault();

    $("#form-wallets").hide();
    $("#containerLoading2").show();

    $.ajax({
      type: "POST",
      url: extradata.ajax,
      data: {
        action: "updateForm",
        peronio_gateway_address: $("#input1").val(),
        peronio_token_address: $("#input2").val(),
        peronio_payment_address: $("#input3").val(),
        peronio_owner_address: $("#input4").val(),
      },
      success: function (response) {
        location.reload();
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        location.reload();
        console.log("Status: " + textStatus + "Error: " + errorThrown);
      },
    });
  });
  $("#button-change").click(async function (e) {
    e.preventDefault();

    if (!activeMetamask) return;

    $("#form-wallets").hide();
    $("#containerLoading2").show();

    //Chain id
    await changeNetwork();

    //Get contracts methods
    await window.ethereum.request({method: "eth_requestAccounts"});

    let contractMethods = new window.web3.eth.Contract(
      abiContractCreateGateway,
      contract_address_create_gateway
    );

    //Get wallet balance
    let tempAccount = await window.web3.eth.getAccounts();

    contractMethods.methods
      .createGateway(extradata.peronio_token_address, $("#input4").val())
      .send({from: tempAccount[0]})
      .then((result) => {
        $.ajax({
          type: "POST",
          url: extradata.ajax,
          data: {
            action: "updateForm",
            peronio_gateway_address: $("#input1").val(),
            peronio_token_address: $("#input2").val(),
            peronio_owner_address: $("#input4").val(),
            peronio_payment_address:
              result.events.GatewayCreated.returnValues.contractAddress,
          },
          success: function (response) {
            location.reload();
          },
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            location.reload();
            console.log("Status: " + textStatus + "Error: " + errorThrown);
          },
        });
      })
      .catch((err) => {
        $("#form-wallets").show();
        $("#containerLoading2").hide();
        console.log(err);
      });
  });
});
