<?php
namespace hb\epay;
class HBepay
{
    public function gateway(
        $hbp_env,
        $hbp_client_id,
        $hbp_client_secret,
        $hbp_terminal,
        $hbp_invoice_id,
        $hbp_amount,
        $hbp_currency = "KZT",
        $hbp_back_link,
        $hbp_failure_back_link,
        $hbp_post_link,
        $hbp_failure_post_link,
        $hbp_language = "RU",
        $hbp_description = "",
        $hbp_account_id = "",
        $hbp_telephone = "",
        $hbp_email = ""
        )
    {
        $test_url = "https://testoauth.homebank.kz/epay2/oauth2/token";
        $prod_url = "https://epay-oauth.homebank.kz/oauth2/token";
        $token_api_url = "";
        $err_exist = false;
        $err = "";

        if ($hbp_env === "test") {
            $token_api_url = $test_url;
        } else {
            $token_api_url = $prod_url;
        }


        $fields = [
            'grant_type'      => 'client_credentials', 
            'scope'           => 'payment',
            'client_id'       => $hbp_client_id,
            'client_secret'   => $hbp_client_secret,
            'invoiceID'       => $hbp_invoice_id,
            'amount'          => $hbp_amount,
            'curency'         => $hbp_currency,
            'terminal'        => $hbp_terminal,
        ];
    
        // build query for request
        $fields_string = http_build_query($fields);
    
        // open connection
        $ch = curl_init();
    
        // set the option
        curl_setopt($ch, CURLOPT_URL, $token_api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        $result = curl_exec($ch);
        $json_result = json_decode($result, true);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    $hbp_access_token = $json_result["access_token"];
                ?>
                    <script src="https://test-epay.homebank.kz/payform/payment-api.js"></script>
                    <script>
                        var createPaymentObject = function(auth, invoiceId, amount, currency) {
                            var paymentObject = {
                                invoiceId: invoiceId,
                                backLink: "<?= $hbp_back_link ?>",
                                failureBackLink: "<?= $hbp_failure_back_link ?>",
                                postLink: "<?= $hbp_post_link ?>",
                                failurePostLink: "<?= $hbp_failure_post_link ?>",
                                language: "<?= $hbp_language ?>",
                                description: "<?= $hbp_description ?>",
                                accountId: "<?= $hbp_account_id ?>",
                                terminal: "<?= $hbp_terminal ?>",
                                amount: amount,
                                currency: currency
                            };
                            paymentObject.auth = auth;
                            return paymentObject;
                        };
                        halyk.pay(createPaymentObject(
                            "<?= $hbp_access_token ?>", 
                            "<?= $hbp_invoice_id ?>" , 
                            "<?= $hbp_amount ?>", 
                            "<?= $hbp_currency ?>"
                            ));
                     </script>
                <?php
                    break;
                default:
                    echo 'Неожиданный код HTTP: ', $http_code, "\n";
            }
        }
    }
}
?>