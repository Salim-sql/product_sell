<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Initialize variables
$start_date = $end_date = null;
$ventes = null;
$total_money_spend = 0;
$total_credit = 0;

// Check if both date inputs are set
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    if (!empty($start_date) && !empty($end_date)) {
        // Query to fetch ventes with joined produit table
        $query = 'SELECT vente.*, produit.nom_produit 
                  FROM vente 
                  JOIN produit ON vente.id_produit = produit.id_produit 
                  WHERE (vente.date_vente > :start_date OR vente.date_vente = :start_date) 
                  AND (vente.date_vente < :end_date OR vente.date_vente = :end_date)';

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total money spent
        $total_spend_query = 'SELECT SUM(
                                  CASE 
                                      WHEN type_payement = "espece" THEN total_vente
                                      WHEN type_payement = "versement" THEN versement
                                      ELSE 0
                                  END
                               ) AS total_money_spend 
                               FROM vente 
                               WHERE (date_vente > :start_date OR date_vente = :start_date) 
                               AND (date_vente < :end_date OR date_vente = :end_date)';
        $total_spend_stmt = $pdo->prepare($total_spend_query);
        $total_spend_stmt->bindParam(':start_date', $start_date);
        $total_spend_stmt->bindParam(':end_date', $end_date);
        $total_spend_stmt->execute();
        $total_spend_result = $total_spend_stmt->fetch(PDO::FETCH_ASSOC);
        $total_money_spend = $total_spend_result['total_money_spend'] ?? 0;

        // Calculate total credit
        $total_credit_query = 'SELECT SUM(
                                  CASE 
                                      WHEN type_payement = "credit" THEN total_vente
                                  WHEN type_payement = "versement" THEN credit

                                      ELSE 0
                                  END
                               ) AS total_credit 
                               FROM vente 
                               WHERE (date_vente > :start_date OR date_vente = :start_date) 
                               AND (date_vente < :end_date OR date_vente = :end_date)';
        $total_credit_stmt = $pdo->prepare($total_credit_query);
        $total_credit_stmt->bindParam(':start_date', $start_date);
        $total_credit_stmt->bindParam(':end_date', $end_date);
        $total_credit_stmt->execute();
        $total_credit_result = $total_credit_stmt->fetch(PDO::FETCH_ASSOC);
        $total_credit = $total_credit_result['total_credit'] ?? 0;
    }
} else {
    $query = 'SELECT vente.*, produit.nom_produit 
              FROM vente 
              JOIN produit ON vente.id_produit = produit.id_produit';

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total money spent for all records
    $total_spend_query = 'SELECT SUM(
                              CASE 
                                  WHEN type_payement = "espece" THEN total_vente
                                  WHEN type_payement = "versement" THEN versement
                                  ELSE 0
                              END
                           ) AS total_money_spend FROM vente';
    $total_spend_stmt = $pdo->prepare($total_spend_query);
    $total_spend_stmt->execute();
    $total_spend_result = $total_spend_stmt->fetch(PDO::FETCH_ASSOC);
    $total_money_spend = $total_spend_result['total_money_spend'] ?? 0;

    // Calculate total credit for all records
    $total_credit_query = 'SELECT SUM(
                              CASE 
                                  WHEN type_payement = "credit" THEN total_vente
                                  WHEN type_payement = "versement" THEN credit
                                  ELSE 0
                              END
                           ) AS total_credit FROM vente';
    $total_credit_stmt = $pdo->prepare($total_credit_query);
    $total_credit_stmt->execute();
    $total_credit_result = $total_credit_stmt->fetch(PDO::FETCH_ASSOC);
    $total_credit = $total_credit_result['total_credit'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Filter Ventes by Date</title>
</head>
<style>
    input[type="date"],
    input[type="button"],
    button {
        width: 40%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        box-sizing: border-box;
        margin-top: 20px;
    }

    * {
        text-align: center;
    }

    table {
        border: 2px solid;
        height: auto;
        width: 80vw;
        margin: 30px auto;
        border-spacing: 0;
        font-family: sans-serif;
    }

    thead {
        background-color: #6be9ff;
    }

    td,
    th {
        border: 2px solid;
        padding: 10px;
        width: 100px;
        text-align: center;
        text-wrap: nowrap;
    }
</style>
<nav class="navbar">
    <ul>
        <li><a style="font-family: fantasy" href="../index.php">Sal_Calc</a></li>
        <li class="dropdown">
            <a href="../affichage/produit.php">Produits</a>
        </li>
        <li class="dropdown">
            <a href="../affichage/fournisseur.php">fournisseurs</a>
        </li>
        <li class="dropdown">
            <a href="../affichage/achat.php">Achats</a>
        </li>
        <li class="dropdown">
            <a href="../affichage/client.php">Clients</a>
        </li>
        <li class="dropdown">
            <a href="../affichage/vente.php">Ventes</a>
        </li>
    </ul>
</nav>
<br><br><br>

<body>
    <form method="GET">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>" required>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>" required>

        <button type="submit">Filter Ventes</button>

        <input type="button" value="Nouvelle vente" onclick="window.location.href='../ajouter/vente.php';">

    </form>
    <br><br><br>

    <!-- Display total money spent and total credit -->
    <h3>Total Money entrante: <?php echo number_format($total_money_spend, 2); ?> </h3> <br><br>
    <h3>Total Credit: <?php echo number_format($total_credit, 2); ?> </h3> <br><br>


    <?php if (isset($ventes) && !empty($ventes)) { ?>
        <h2>Ventes from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>:</h2>

        <table border="1">
            <thead>
                <th>nom Produit</th>
                <th>Nom Client</th>
                <th>Localisation</th>
                <th>Quantité Vendue</th>
                <th>Prix Unitaire Vendue</th>
                <th>Total Vente</th>
                <th>Type_P</th>
                <th>Versement</th>
                <th>Credit</th>
                <th> total Bénéfice</th>
                <th>Date Vente</th>
                <th>Action</th>
            </thead>
            <?php foreach ($ventes as $vente) { ?>
                <tr <?php if ($vente['type_payement'] == 'versement') {
                        echo "style='background-color:#a0c7a0'";
                    } elseif ($vente['type_payement'] == 'credit') {
                        echo "style='background-color:#ff7575'";
                    } ?>>
                    <td><?php echo htmlspecialchars($vente['nom_produit']); ?></td>
                    <td><?php echo htmlspecialchars($vente['nom_client']); ?></td>
                    <td><?php echo htmlspecialchars($vente['localisation']); ?></td>
                    <td><?php echo htmlspecialchars($vente['qte_vendue']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($vente['pu_vendue'], 2)); ?></td>
                    <td><?php echo htmlspecialchars(number_format($vente['total_vente'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($vente['type_payement']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($vente['versement'], 2)); ?></td>
                    <td><?php echo htmlspecialchars(number_format($vente['credit'], 2)); ?></td>
                    <td><?php echo htmlspecialchars(number_format($vente['benifice_total'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($vente['date_vente']); ?></td>
                    <td><a style="color: blue;" href="../modifier/vente.php?id=<?php echo $vente['id_vente']; ?>">modifier</a>
                        <a style="color: red;" href="../supprimer/vente.php?id=<?php echo $vente['id_vente']; ?>" onclick="return confirm('Are you sure you want to delete this Vente?');">Supprimer</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } elseif ($start_date && $end_date) { ?>
        <p>No ventes found between <?php echo htmlspecialchars($start_date); ?> and <?php echo htmlspecialchars($end_date); ?>.</p>
    <?php } ?>
</body>

</html>