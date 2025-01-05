<?php

class Orderbacklog
{

    // database connection and table name
    private $conn;
    private $table_name = "orderbacklog";

    // object properties
    public $id;
    public $name;
    public $ts;

    /**
     * @var int $tobedone Status des Backlog-Eintrags (1 = Aufgabe noch ausstehend).
     */
    public $tobedone;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    /**
     * Fügt einen neuen Backlog-Eintrag in die Datenbank ein.
     *
     * @return int Die ID des neuen Backlog-Eintrags.
     *
     * @throws PDOException Wenn ein Fehler während der Datenbankoperation auftritt.
     */
    public function addStorage() {
        $query = "insert into orderbacklog (name, ts, tobedone) values(?, ?, ?)";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindValue(1, $this->name);
        $stmt->bindValue(2, $this->ts);
        $stmt->bindValue(3, $this->tobedone);

        if(!$stmt->execute()) print_r($stmt->errorInfo());

        $id=0;
        $sql = "SELECT currval('orderbacklog_id_seq') as thisid";
        $stmt = $this->conn->prepare( $sql );
        if(!$stmt->execute()) print_r($stmt->errorInfo());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $id = $row['thisid'];
        }
        return $id;


    }


    /**
     * Marks an order as completed in the backlog by updating the 'tobedone' status to 0.
     * The method checks for a specific order by its ID and updates its status if it is marked as pending (tobedone = 1).
     *
     * @return bool Returns true if the order was successfully updated, false if no matching order was found or updated.
     */
    function markOrderById() {
        $sql = "select * from orderbacklog where id = ? and tobedone = 1";
        $stmt = $this->conn->prepare( $sql );
        $stmt->bindValue(1, $this->id);
        $stmt->execute();
        $found=false;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $sqlu = "update orderbacklog set tobedone = 0 where id = ?";
            $stmt2 = $this->conn->prepare( $sqlu );
            $stmt2->bindValue(1, $this->id);
            $stmt2->execute();
            $found=true;
        }
        return $found;
    }


    /**
     * Liest alle Backlog-Daten aus der Datenbank, die noch ausstehend sind (tobedone = 1).
     *
     * @param string $output Format des Rückgabewerts ('voice' oder 'array').
     * @return string|array Für 'voice' wird ein sprachlicher String zurückgegeben.
     *                      Für 'array' wird ein Array mit den Backlog-Daten zurückgegeben.
     */
    public function readAll($output = 'voice') {
        $query = "SELECT * FROM orderbacklog where tobedone = 1 order by ts";
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        if($output == 'voice') {
            $returnvalue="";
        } else {
            $returnvalue=array();
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($output == 'voice') {
                if (strlen($returnvalue) > 0) $returnvalue .= ", ";
                $returnvalue .= "Nummer " . $row['id'] . ": " . $row['name'] . " vom " . date('d.m.Y H:i', strtotime($row['ts']));
            } else {
                $returnvalue[] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'ts' => date('Y-m-d H:i:s', strtotime($row['ts'])),
                    'tobedone' => $row['tobedone']
                );
            }
        }
        return $returnvalue;
    }


}
