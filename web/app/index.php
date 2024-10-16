<?php
error_reporting(E_ERROR);
//phpinfo();
if(!isset($json)) {
    $json=array(
        "talk"=>'',
        "action"=>''
    );
}
if(isset($_POST['theaction'])) {
    //$jsonpost = '[{"queryaction":"einlagern led","workflow":"add","workflowstep":1,"suchstring":"led","talk":"Artikel gefunden, bitte Menge angeben","productname":"LED blau 5mm","product_id":92,"followupworkflowstep":3,"nextaction":"queryamount"}]';
    //var_dump($_POST['json']);
    //var_dump($_POST);
    //echo "<hr>";
    $jsonpost=str_replace("'",'"',$_POST['json']);
    //var_dump($jsonpost);
    //echo "<hr>";
    $json=json_decode($jsonpost, true);

    //var_dump($json);
    //echo "<hr>";

    $json['action']=$_POST['theaction'];
    if(isset($json['followupworkflowstep'])) {
        $json['workflowstep']=$json['followupworkflowstep'];
    }
    //var_dump($json);
    //exit;
    $ch = curl_init();
	// TODO: set to webserver address
    curl_setopt($ch, CURLOPT_URL, "http://WEBHOST/storagelocation/talk.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    $data = curl_exec($ch);
    curl_close($ch);
    //var_dump($data);
    $json=json_decode($data, true);
    //var_dump($json);
}



?>
<html lang="de">
<head>
    <title>ERP APP</title>
    <link href="css/bootstrap.css" rel="stylesheet" crossorigin="anonymous">
    <script src="js/bootstrap.bundle.js" crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="datatables/DataTables-1.13.2/css/dataTables.bootstrap5.css"/>
    <link rel="stylesheet" type="text/css" href="datatables/Responsive-2.4.0/css/responsive.bootstrap5.css"/>

    <script type="text/javascript" src="datatables/jQuery-3.6.0/jquery-3.6.0.js"></script>
    <script type="text/javascript" src="datatables/DataTables-1.13.2/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="datatables/DataTables-1.13.2/js/dataTables.bootstrap5.js"></script>
    <script type="text/javascript" src="datatables/Responsive-2.4.0/js/dataTables.responsive.js"></script>
    <script type="text/javascript" src="datatables/Responsive-2.4.0/js/responsive.bootstrap5.js"></script>

    <link rel="shortcut icon" href="../favicon.png" type="image/png" />
    <link rel="icon" href="../favicon.png" type="image/png" />

</head>
<body style="margin:5px;padding:5px">

<form name="theform" action="index.php" method="post" onsubmit="return isValidForm()">

    <div class="row g-3 align-items-center">
        <div class="col-auto"><h3>API-ERP</h3>
        </div>

    </div>
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <label for="inputquery" class="col-form-label">Aktion:</label>
        </div>
        <div class="col-auto">
            <input type="text" id="inputquery" class="form-control" name="theaction" >
        </div>
    </div>
    <div class="row g-3 align-items-center">
        <div class="col-auto">Ausgabe:
        </div>
        <div class="col-auto"><?php echo $json['talk'];?>
        </div>

    </div>

    <?php
    if(isset($json['nextaction']) && $json['nextaction']=='exit') {
        $json=array(
            "talk"=>'',
            "action"=>''
        );
    }
    ?>

    <input type="hidden" name="json" value="<?php echo(str_replace('"',"'",json_encode($json)));?>">
    <hr>
    <pre><?php echo print_r($json, true);?></pre>
</form>
<hr>
<a href="index.php" type="button" class="btn btn-secondary">Reset</a>&nbsp;&nbsp;&nbsp;<a type="button" class="btn btn-primary" href="javascript:fireSubmit()">Submit</a>
<hr>
<script language="JavaScript">
    document.theform.theaction.focus();
    function fireSubmit() {
        if(isValidForm()) {
            document.theform.submit();
        }
    }

    function isValidForm() {
        if(document.theform.theaction.value=='') {
            return false;
        }
        return true;
    }
    function triggerEinlagern(productname) {
        document.theform.theaction.value="Einlagern "+productname;
        document.theform.json='';
        if(isValidForm()) {
            document.theform.submit();
        }
    }

</script>

<?php
if(!isset($json['nextaction'])) {
?>
  <script>
      $(document).ready(function () {
          $('#mytable').DataTable({
              ajax: '../storagelocation/liststock.php',
              sAjaxDataProp: "data",
              iDisplayLength: 100,
              order: [[1, 'asc']],
              columns: [
                  { data: 'amount' },
                  { data: 'productname',
                      "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                          $(nTd).html("<a href='javascript:triggerEinlagern(\""+oData.productname+"\")'>"+oData.productname+"</a>");
                      } },
                  { data: 'storagename' },
                  { data: 'storagelocationname' }
              ],
              "language": {
                  "sEmptyTable": "Keine Daten in der Tabelle vorhanden",
                  "sInfo": "_START_ bis _END_ von _TOTAL_ Einträgen",
                  "sInfoEmpty": "0 bis 0 von 0 Einträgen",
                  "sInfoFiltered": "(gefiltert von _MAX_ Einträgen)",
                  "sInfoPostFix": "",
                  "sInfoThousands": ".",
                  "sLengthMenu": "_MENU_ Einträge anzeigen",
                  "sLoadingRecords": "Wird geladen...",
                  "sProcessing": "Bitte warten...",
                  "sSearch": "Suchen",
                  "sZeroRecords": "Keine Einträge vorhanden.",
                  "oPaginate": {
                      "sFirst": "Erste",
                      "sPrevious": "Zurück",
                      "sNext": "Nächste",
                      "sLast": "Letzte"
                  }
              }
          });
      });
  </script>

    <div class="flex-row row">
        <div class="col-lg-12 col-sm-12" style="position: relative;">
            <table id="mytable" class="display" style="width:100%">
                <thead>
                <tr>
                    <th>Menge</th>
                    <th>Produkt</th>
                    <th>Lagerort</th>
                    <th>Lagerort in</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>Menge</th>
                    <th>Produkt</th>
                    <th>Lagerort</th>
                    <th>Lagerort in</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

<?php
}
?>




</body>
</html>
