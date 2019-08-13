<?php


#####################################################
# Autor --> JOSÉ BORGES
# MONITORAMENTO SLA ZABBIX
###################################################


require_once 'conect_zabbix.php';

//PARAMENTROS

$OPT = $argv[1];

class SLAMONITORING {

	private $con;


	function __construct($con){ //Construtor 

		$this->con = $con;
	}

//Metodo  para exibição do percentual de SLA

	public function getSLA($idsla,$from,$to) {  
		$dadosla = $this->con->serviceGetSla(array(

			"serviceids" => $idsla,
			'intervals' => array(
				'from' => $from, 
				'to' =>  $to 
			)
		));

		foreach ($dadosla as $value)
			$percslas = (string) $value->sla[0]->sla;
		$percsla = substr($percslas,0,5);
		return $percsla;

	}
	
// Metodo para Exibição do nome SLA que começão com R
		
	public function getNameSLA() {  
		$dados = $this->con->serviceGet(array(


			"filter" => array("triggerid" => "0"),

			'output' => array("name")
		));

		$nameSla = array();
		for($i=0;$i<count($dados);$i++){

			$contains = preg_match('/^R/',$dados[$i]->name);

			if($contains === 1) {

				$nameSla[]= $dados[$i]->name;


			}


		}

		return $nameSla;
	}

// Metodo que exibe o ID do SLA

	public function getSLAID($nameSLA){ 

		$Slaid = $this->con->serviceGet(array(

			"filter" => array(

				"name" => $nameSLA
			),

			"output" => array("serviceid")

		));

		return $Slaid;

	}


}


$dados = new SLAMONITORING($api);

switch($OPT){

// Case JSON para Discovery dos Items

	case 'SLANAME':
	
	$dadosname = $dados->getNameSLA();
	$data = array('data'=> array());
	$i = 0;
	foreach ($dadosname as $valuesIndex => $values) {
		$data['data'][$i]['{#SLANAME}'] = $values;
		
		$i++;

	}

	print json_encode($data);
	echo "\n";	
	break;
// Case exibição percentual de SLA de 01 do mes corrente ao ultimo dia do mesmo mes

	case 'VSLA':
	
	$SLANAME = $argv[2];

	$dateAtual = new DateTime();
	$horaAtual = $dateAtual->format('H:i:s');
	$mesAnoAtual = $dateAtual->format('Y-m');
	
	//From
	$hdmA = $mesAnoAtual.'-01 '.$horaAtual;
	$NhdmA = new DateTime($hdmA);
	$timeStampFrom = $NhdmA->getTimestamp();

	//TO
	$udmesAtual = $dateAtual->format('Y-m-t ').$horaAtual;
	$NudmesAtual = new DateTime($udmesAtual);
	$timeStampTo = $NudmesAtual->getTimestamp();

	$idparament = $dados->getSLAID($SLANAME)[0]->serviceid;

	$percent_SLA = $dados->getSLA($idparament,$timeStampFrom,$timeStampTo);

	print_r($percent_SLA);
	echo "\n";
	
	break;
	default:
	break;

}



?>