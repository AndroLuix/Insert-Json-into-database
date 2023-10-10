<?php

namespace JsonData;

class DataInserter
{
    private $connessione;
    private $inserimento;
    private $verificaEsistenza;

    public function __construct($connessione, $entita, $colonne)
    {
        $this->connessione = $connessione;
        $this->entita = $entita;

        $columnNames = implode(', ', $colonne);
        $valori = implode(', ', array_fill(0, count($colonne), '?'));

        $query = "INSERT INTO $entita ($columnNames) VALUES ($valori)";
        $this->inserimento = $this->connessione->prepare($query);

        $bindParams = str_repeat("s", count($colonne)); // Assuming all string types
        $this->inserimento->bind_param($bindParams, ...$this->getColonne($colonne));

        $this->verificaEsistenza = function ($entita, $valori) use ($connessione) {
            $query = "SELECT COUNT(*) AS conteggio FROM $entita WHERE ";
            $condizioni = [];
            foreach ($valori as $colonna => $valore) {
                $condizioni[] = "$colonna = ?";
            }
            $query .= implode(' AND ', $condizioni);

            $stmt = $connessione->prepare($query);
            if (!$stmt) {
                return false;
            }

            // Bind dei valori dei parametri
            $bindParams = str_repeat("s", count($valori));
            $bindValues = array_values($valori);
            $stmt->bind_param($bindParams, ...$bindValues);

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['conteggio'] > 0;
        };
    }


    public function verificaEsistenza($entita, $valori)
    {
        // Rimani nel contesto dell'oggetto utilizzando $this->connessione invece di $connessione
        $query = "SELECT COUNT(*) AS conteggio FROM $entita WHERE ";
        $condizioni = [];
        foreach ($valori as $colonna => $valore) {
            $condizioni[] = "$colonna = ?";
        }
        $query .= implode(' AND ', $condizioni);

        $stmt = $this->connessione->prepare($query);
        if (!$stmt) {
            return false;
        }
    }

    private function getColonne($colonne)
    {
        $arrayColonne = [];
        foreach ($colonne as $column) {
            $arrayColonne[] = &$this->$column;
        }
        return $arrayColonne;
    }

    public function insertDataFromJSON($jsonFilePath, $values)
    {
        $dati_json = file_get_contents($jsonFilePath);
        $data = json_decode($dati_json, true);

        $riga = 0;
        $messaggio_errore = "";
        foreach ($data as $d) {
            // Debug output
            echo "Valori JSON: " . json_encode($d, JSON_PRETTY_PRINT) . "<br>";

            $valuesComplete = true; // Aggiunto un flag per indicare se tutti i valori sono presenti
            foreach ($values as $property => $jsonKey) {
                if (isset($d[$jsonKey])) {
                    $this->$property = $d[$jsonKey];
                    echo "Assegnato valore per '$property': " . $d[$jsonKey] . "<br>";
                } else {
                    $messaggio_errore .= "L'indice '$jsonKey' non è stato trovato per l'oggetto: <br> <pre>" . json_encode($d, JSON_PRETTY_PRINT) .
                        " nell'entità $this->entita</pre><br>";
                    $valuesComplete = false; // Imposta il flag su false se almeno un valore è mancante
                }
            }

            // Esegui l'inserimento solo se tutti i valori sono stati assegnati
            if ($valuesComplete && !$this->inserimento->execute()) {
                // Se c'è un errore nell'inserimento, ottieni e stampa l'errore dal database
                $messaggio_errore .= '<span style="background-color: lightblue;">Errore nell\'inserimento nel database: ' . $this->connessione->error . '</span><br>';
            }

            $riga++;
        }


        if (!empty($messaggio_errore)) {
            return $messaggio_errore;
        }

        if (count($data) == $riga) {
            return " Operazione eseguita con successo in " . $this->entita;
        } else {
            return " Errore, operazione fallita";
        }
    }

}