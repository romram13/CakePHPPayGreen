<?php
class PaygreenClient
{
    const VERSION = '0.8B';
    const CURRENCY_EUR = 'EUR';


    const STATUS_WAITING            = "WAITING";
    const STATUS_PENDING            = "PENDING";
    const STATUS_EXPIRED            = 'EXPIRED';
    const STATUS_PENDING_EXEC       = "PENDING_EXEC";
    const STATUS_WAITING_EXEC       = "WAITING_EXEC";
    const STATUS_CANCELLING         = "CANCELLED";
    const STATUS_REFUSED            = "REFUSED";
    const STATUS_SUCCESSED          = "SUCCESSED";
    const STATUS_RESETED            = "RESETED";
    const STATUS_REFUNDED           = "REFUNDED";
    const STATUS_FAILED             = "FAILED";

    const MODE_CASH                 = "CASH";
    const MODE_RECURRING            = "RECURRING";
    const MODE_TOKENIZE             = "TOKENIZE";

    const RECURRING_DAILY = 10;
    const RECURRING_WEEKLY = 20;
    const RECURRING_SEMI_MONTHLY = 30;
    const RECURRING_MONTHLY = 40;
    const RECURRING_BIMONTHLY = 50;
    const RECURRING_QUARTERLY = 60;
    const RECURRING_SEMI_ANNUAL = 70;
    const RECURRING_ANNUAL = 80;
    const RECURRING_BIANNUAL = 90;

    public static $RECURRING_LABEL = array(
        self::RECURRING_DAILY => 'jour',
        self::RECURRING_WEEKLY => 'semaine',
        self::RECURRING_SEMI_MONTHLY => 'quinzaine',
        self::RECURRING_MONTHLY => 'mois',
        self::RECURRING_BIMONTHLY => '2 mois',
        self::RECURRING_QUARTERLY => '4 mois',
        self::RECURRING_SEMI_ANNUAL => 'semestre',
        self::RECURRING_ANNUAL => 'an',
        self::RECURRING_BIANNUAL => '2 ans'
    );

    private static $host = "https://paygreen.fr/paiement/new/";

    protected $token;
    protected $key;
    protected $data = array();

    public function __construct($encryptKey, $rootUrl = null)
    {
        $this->key = $encryptKey;

        if($rootUrl != null)
            self::$host =  $rootUrl.'/paiement/new/';
    }

    public function privateKey($encryptKey)
    {
        $this->key = $encryptKey;
    }

    public function setToken($shopToken)
    {
        $this->token = base64_encode(time().":".$shopToken);
    }

    public function parseToken($token)
    {
        $this->token = $token;
        return explode(':',base64_decode($token));
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function toArray()
    {
        return $this->data;
    }

    public function mergeData($data)
    {
      $this->data = array_merge($this->data, $data);
    }

    public function isAccepted() {
        if(!array_key_exists('result', $this->data))
            return -1;
        return $this->data['result']['status'] == self::STATUS_SUCCESSED;
    }


    public function parseData($post)
    {
        $text = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $this->key, base64_decode($post), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
        $this->data = json_decode(utf8_decode($text), true);
    }

    public function generateData()
    {
        $text = utf8_encode(json_encode($this->data));
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $this->key, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

    public function getActionForm()
    {
        return self::$host.$this->token;
    }

    public function renderForm()
    {
        ?>
        <form method="post" action="<?php echo $this->getActionForm(); ?>" name="PAYGREEN">
            <input type="hidden" name="data" value="<?php echo $this->generateData(); ?>?>" />
            <input type="submit" value="Payer" />
        </form>
        <?php
    }

    public function returnedUrl($returned, $notification, $cancelled = null) {
        $this->return_url = $returned;
        $this->return_callback_url = $notification;
        $this->return_cancel_url = $cancelled != null ? $cancelled : $returned;
        return $this;

    }

    public function customer($id, $last_name, $first_name, $email, $country = "FR")
    {
        $this->customer_id = $id;
        $this->customer_last_name = $last_name;
        $this->customer_first_name = $first_name;
        $this->customer_email = $email;
        $this->customer_country = $country;
        return $this;
    }

    public function immediatePaiement($transactionId, $amount, $currency = self::CURRENCY_EUR)
    {
        return $this->transaction($transactionId, $amount, $currency);
    }

    public function transaction($transactionId, $amount, $currency = self::CURRENCY_EUR)
    {
        $this->transaction_id = $transactionId;
        $this->mode = self::MODE_CASH;
        $this->amount = $amount;
        $this->currency = $currency;
        return $this;
    }

    public function cardPrint() {
        $this->mode = self::MODE_TOKENIZE;
        return $this;
    }

    public function additionalTransaction($amount) {
        if($this->mode == self::MODE_RECURRING) {
            $this->additionalAmount = $amount;
        } else {
            throw new \Exception("Cette fonction est utilisable uniquement avec une transaction de type reccurence", 1);
        }
        return $this;
    }

   public function subscribtionPaiement($reccuringMode = null, $dueCount = null, $transactionDay = -1, $startAt = null) 
    {
        $this->mode = self::MODE_RECURRING;
        if($reccuringMode != null) {
            $this->reccuringMode = $reccuringMode;
            $this->reccuringDueCount = $dueCount;
            $this->reccuringTransactionDay = $transactionDay;
            $this->reccuringStartAt = $startAt;
        }
        
        return $this;
    }

    public function subscriptionFirstAmount($firstAmount, $firstAmountDate = null) {
        $this->reccuringFirstAmount = $firstAmount;
        $this->reccuringFirstAmountDate = $firstAmountDate;
    }

    public function xTimePaiement($nbPaiement) {
        $amount = $this->amount;
        $currency = $this->currency;

        if($nbPaiement>1) {
            $occurenceAmount = floor($amount / $nbPaiement);
            $firstAmount  = $amount - ($occurenceAmount * ($nbPaiement-1));

            $this->subscribtionPaiement(
                $this::RECURRING_MONTHLY,
                $nbPaiement,
                date('d')
            );
            if($occurenceAmount != $firstAmount) {
                $this->subscriptionFirstAmount(
                    $firstAmount
                );
            }
            $this->amount = $occurenceAmount;
        }

        
    }
}

?>