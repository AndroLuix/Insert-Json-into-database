<?php
require_once 'DataInserter.php';

use JsonData\DataInserter;

$br = '<br>';

$connessione = new mysqli('localhost', 'root', '', 'test');
if ($connessione->connect_errno != 0) {
    echo $connessione->connect_error;
} else {
    echo 'connesso';
}
$id_clienti = $connessione->query("select id from clienti_indirizzi ");
while ($row = $id_clienti->fetch_assoc()) {
    echo "ID: " . $row["id"] . "<br>";
}
$inserimenti = [

    //Inizio inserimento dati nell'entità depositi
    [
        'entita' => 'depositi',
        'colonne' => ["id_deposito", "deposito"],
        'values' => [
            "id_deposito" => "id_deposito",
            "deposito" => "descr_dep"
        ],
        'jsonFile' => 'samples/schema/storages.json'
    ],


    //Inizio inserimento dati nell'entità agenti
    [
        'entita' => 'agenti',
        'colonne' => ["id", "agente"],
        'values' => [
            "id" => "id",
            "agente" => "agente",
        ],
        'jsonFile' => 'samples/schema/agents.json'
    ],
    //Fine inserimento


    //Inizio inserimento dati nell'entità categorie
    [
        'entita' => 'categorie',
        'colonne' => ["id", "parent"],
        'values' => [
            "id" => "id",
            "parent" => "parent",
        ],
        'jsonFile' => 'samples/schema/categories.json'
    ],
    //Fine inserimento

    //inizio inserimento dati nell'entità clienti

    [
        'entita' => 'clienti',
        'colonne' => ['id_agente', 'id_depositi', 'ragione_sociale', 'codice_fiscale', 'piva'],
        'values' => [
            "id_agente" => "agente",
            "id_deposito" => "id_deposito",
            "ragione_sociale" => "ragione_sociale",
            "codice_fiscale" => "codice_fiscale",
            "piva" => "piva",
        ],
        'jsonFile' => 'samples/schema/clients.json'
    ],

    //fine inseriento dati

    //Inizio inserimento dati clienti_indirizzi (clientsAddresses)
    [
        'entita' => 'clienti_indirizzi',
        'colonne' => ['via', 'citta', 'provincia', 'cap', 'stato', 'note'],
        'values' => [
            'via' => 'via',
            'citta' => 'citta',
            'provincia' => 'provincia',
            'cap' => 'cap',
            'stato' => 'stato',
            'note' => 'note',
        ],
        'jsonFile' => 'samples/schema/clientsAddresses.json'
    ]

];


foreach ($inserimenti as $inserimento) {
    $Inserimento = new DataInserter($connessione, $inserimento['entita'], $inserimento['colonne']);
    if (!$Inserimento->verificaEsistenza($inserimento['entita'], $inserimento['values'])) {
        $result = $Inserimento->insertDataFromJSON($inserimento['jsonFile'], $inserimento['values'], true);
        echo $br . $result;
    } else {
        echo "Dati già presenti per {$inserimento['entita']}<br>";
    }

    //debug output
    // Debug output
    echo "Inserimento per entità: " . $inserimento['entita'] . "<br>";
    echo "Colonne: " . implode(', ', $inserimento['colonne']) . "<br>";
    echo "Valori: " . implode(', ', $inserimento['values']) . "<br>";
    echo "JSON File: " . $inserimento['jsonFile'] . "<br>";

    $result = $Inserimento->insertDataFromJSON($inserimento['jsonFile'], $inserimento['values'],true);
    echo $br . $result;
}

// refresh della pagina
$time_refresh = 60 * 5;
$url = $_SERVER['PHP_SELF'];
//header("Refresh: {$time_refresh}; url={$url}");
?>
