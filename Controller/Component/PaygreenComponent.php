<?php
App::uses('Component', 'Controller');
App::uses('PaygreenClient', 'Paygreen.Lib');

class PaygreenComponent extends Component {

    public $Controller;

    public $paiement;

	protected $config = [
		'paygreen_privatekey'   => '',
		'paygreen_publickey'    => true,
		'amount'                => 0.00,
        'id'                    => 0,
        'lastname'              => 'PAY',
        'firstname'             => 'Green',
        'email'                 => 'email@email.com',
        'actionReturn'          => 'return', //Action utilisée pour le retour de paiement
        'actionNotification'    => 'notification', //Action utilisée pour le retour de paiement
        'actionCancel'          => 'cancel' //Action utilisée pour le retour de paiement

	];



	public function initialize(Controller $Controller) {
		$this->Controller = $Controller;

	}

    public function configuration($config) {
        $this->config = [
            'paygreen_privatekey'   => $config['paygreen_privatekey'],
            'paygreen_publickey'    => $config['paygreen_publickey'],
            'amount'                => $config['amount'],
            'id'                    => $config['id'],
            'lastname'              => $config['lastname'],
            'firstname'             => $config['firstname'],
            'email'                 => $config['email'],
            'actionReturn'          => $config['actionReturn'], //Action utilisée pour le retour de paiement
            'actionNotification'    => $config['actionNotification'], //Action utilisée pour le retour de paiement
            'actionCancel'          => $config['actionCancel'] //Action utilisée pour le retour de paiement

        ];
    }

    public function PaymentRedirect () {
        $this->paiement = new PaygreenClient($this->config['paygreen_privatekey']);
        $this->paiement->setToken($this->config['paygreen_publickey']);

        //$montant_du_panier = $this->Session->read('form.data.Participant.prix');
        $this->paiement->customer($this->config['id'], $this->config['lastname'], $this->config['firstname'], $this->config['email']);
        $this->paiement->transaction($this->config['id'], $this->config['amount']*100, PaygreenClient::CURRENCY_EUR);
        $this->paiement->returnedUrl (
            Router::url(array('controller'=> $this->Controller->name, 'action'=>$this->config['actionReturn'], $this->config['id']),true),
            Router::url(array('controller'=> $this->Controller->name, 'action'=>$this->config['actionNotification'], $this->config['id']),true),
            Router::url(array('controller'=> $this->Controller->name, 'action'=>$this->config['actionCancel'], $this->config['id']),true) 
        );

        $url = $this->paiement->getActionForm();
        $data = urlencode($this->paiement->generateData());

        $this->Controller->redirect($url.'?d='.$data);
    }
}