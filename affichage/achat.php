<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Initialize variables
$start_date = $end_date = null;
$achats = null;
$total_money_spend = 0;

// Check if both date inputs are set
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    if (!empty($start_date) && !empty($end_date)) {
        // Query to fetch achats with joined produit table
        $query = 'SELECT achat.*, produit.nom_produit 
                  FROM achat 
                  JOIN produit ON achat.id_produit = produit.id_produit 
                  WHERE (achat.date_achat > :start_date OR achat.date_achat = :start_date) 
                  AND (achat.date_achat < :end_date OR achat.date_achat = :end_date)';

        // Prepare the statement
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Query to calculate the total money spent within the date range based on type_payement
        $total_query = 'SELECT SUM(
                           CASE 
                               WHEN type_payement = "espece" THEN total_achat
                               WHEN type_payement = "versement" THEN versement
                               ELSE 0
                           END
                        ) AS total_money_spend 
                        FROM achat 
                        WHERE (date_achat > :start_date OR date_achat = :start_date) 
                        AND (date_achat < :end_date OR date_achat = :end_date)';
        $total_stmt = $pdo->prepare($total_query);
        $total_stmt->bindParam(':start_date', $start_date);
        $total_stmt->bindParam(':end_date', $end_date);
        $total_stmt->execute();
        $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
        $total_money_spend = $total_result['total_money_spend'] ?? 0;

        // Calculate total credit
        $total_credit_query = 'SELECT SUM(
            CASE 
                WHEN type_payement = "credit" THEN total_achat
                WHEN type_payement = "versement" THEN credit
                ELSE 0
            END
         ) AS total_credit 
         FROM achat 
         WHERE (date_achat > :start_date OR date_achat = :start_date) 
         AND (date_achat < :end_date OR date_achat = :end_date)';
        $total_credit_stmt = $pdo->prepare($total_credit_query);
        $total_credit_stmt->bindParam(':start_date', $start_date);
        $total_credit_stmt->bindParam(':end_date', $end_date);
        $total_credit_stmt->execute();
        $total_credit_result = $total_credit_stmt->fetch(PDO::FETCH_ASSOC);
        $total_credit = $total_credit_result['total_credit'] ?? 0;
    }
} else {
    $query = 'SELECT achat.*, produit.nom_produit 
              FROM achat 
              JOIN produit ON achat.id_produit = produit.id_produit';
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query to calculate total money spent for all records
    $total_query = 'SELECT SUM(
                       CASE 
                           WHEN type_payement = "espece" THEN total_achat
                           WHEN type_payement = "versement" THEN versement
                           ELSE 0
                       END
                    ) AS total_money_spend FROM achat';
    $total_stmt = $pdo->prepare($total_query);
    $total_stmt->execute();
    $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total_money_spend = $total_result['total_money_spend'] ?? 0;

    // Calculate total credit for all records
    $total_credit_query = 'SELECT SUM(
        CASE 
            WHEN type_payement = "credit" THEN total_achat
                            WHEN type_payement = "versement" THEN credit
            ELSE 0
        END
     ) AS total_credit FROM achat';
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
    <title>Filter Achats by Date</title>
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
            <a href="../affichage/fournisseur.php">fournisseur</a>
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
        <label for="start_date">Start Date :</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>" required>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>" required>

        <button type="submit">Filter Achats</button>
        <input type="button" value="Nouvelle Achat" onclick="window.location.href = '../ajouter/achat.php'">

    </form>
    <br><br><br>

    <h3>Total money sortante: <?php echo number_format($total_money_spend, 2); ?></h3>
    <br><br>
    <h3>Total Credit: <?php echo number_format($total_credit, 2); ?></h3>
    <br><br>
    <?php if (isset($achats) && !empty($achats)) { ?>
        <h2>Achats from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>:</h2>
        <br>

        <table>
            <thead>
                <th>Code Produit</th>
                <th>Nom fournisseur</th>
                <th>Quantité Achétée</th>
                <th>Prix U Achat</th>
                <th>Total Achat</th>
                <th>Type Payement</th>
                <th>Versement</th>
                <th>Crédit</th>
                <th>Date Achat</th>
                <th>action</th>

            </thead>
            <?php foreach ($achats as $achat) { ?>
                <tr <?php if ($achat['type_payement'] == 'versement') {
                        echo "style='background-color:#a0c7a0'";
                    } elseif ($achat['type_payement'] == 'credit') {
                        echo "style='background-color:#ff7575'";
                    } ?>>
                    <td><?php echo htmlspecialchars($achat['nom_produit']); ?></td>
                    <td><?php echo htmlspecialchars($achat['nom_fournisseur']); ?></td>
                    <td><?php echo htmlspecialchars($achat['qte_acheter']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($achat['pu_acheter'], 2)); ?></td>
                    <td><?php echo htmlspecialchars(number_format($achat['total_achat'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($achat['type_payement']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($achat['versement'], 2)); ?></td>
                    <td><?php echo htmlspecialchars(number_format($achat['credit'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($achat['date_achat']); ?></td>
                    <td><a style="color: blue;" href="../modifier/achat.php?id=<?php echo $achat['id_achat']; ?>">modifier</a>
                        <a style="color: red;" href="../supprimer/achat.php?id=<?php echo $achat['id_achat']; ?>" onclick="return confirm('Are you sure you want to delete this achat?');">Supprimer</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } elseif ($start_date && $end_date) { ?>
        <p>No achats found between <?php echo htmlspecialchars($start_date); ?> and <?php echo htmlspecialchars($end_date); ?>.</p>
    <?php } ?>
</body>

</html>