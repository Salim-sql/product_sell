<?php
try {
    include_once "../db.inc/connection.php";

    // Query to fetch all products
    $query = 'SELECT * FROM produit';
    $stat = $pdo->prepare($query);
    $stat->execute();
    $result = $stat->fetchAll(PDO::FETCH_ASSOC);

    // Query to calculate the total sum of benifice_produit for all products
    $total_benifice_query = 'SELECT SUM(benifice_produit) AS total_benifice FROM produit';
    $total_benifice_stat = $pdo->prepare($total_benifice_query);
    $total_benifice_stat->execute();
    $total_benifice_result = $total_benifice_stat->fetch(PDO::FETCH_ASSOC);
    $total_benifice = $total_benifice_result['total_benifice'] ?? 0;  // Default to 0 if no benifice

} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Document</title>
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
        white-space: nowrap;
    }
</style>

<body>
    <nav class="navbar">
        <ul>
            <li><a style="font-family: fantasy" href="../index.php">Sal_Calc</a></li>
            <li><a href="../affichage/produit.php">Produits</a></li>
            <li><a href="../affichage/fournisseur.php">Fournisseurs</a></li>
            <li><a href="../affichage/achat.php">Achats</a></li>
            <li><a href="../affichage/client.php">Clients</a></li>
            <li><a href="../affichage/vente.php">Ventes</a></li>
        </ul>
    </nav>

    <!-- Display total benifice at the top -->
    <h3 style="text-align: center; margin-top: 20px;">
        Total Bénéfice de tous les produits: <?php echo number_format($total_benifice, 2); ?>
    </h3>

    <?php
    $all_benifice = 0; // Initialize total benefit variable
    $total_valeur_da = 0; // Initialize total stock value in DA

    if (!empty($result)) { ?>
        <h2 style="text-align: center; margin-top: 30px;">Les Produits</h2>

        <table>
            <thead>
                <tr>
                    <th>Nom du produit</th>
                    <th>Quantité achetée</th>
                    <th>Quantité vendue</th>
                    <th>Quantité en stock</th>
                    <th>Bénéfice du produit</th>
                    <th>Total Bénéfice des Ventes</th>
                    <th>Valeur en DA (Stock * Valeur par DA)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) {
                    $id_produit = $row['id_produit'];

                    // Query to retrieve the total benefit for this product from the vente table
                    $vente_query = 'SELECT SUM(benifice_total) AS total_benifice_vente 
                                    FROM vente 
                                    WHERE id_produit = :id_produit';
                    $vente_stat = $pdo->prepare($vente_query);
                    $vente_stat->execute([':id_produit' => $id_produit]);
                    $vente_result = $vente_stat->fetch(PDO::FETCH_ASSOC);
                    $total_benifice_vente = $vente_result['total_benifice_vente'] ?? 0;

                    // Add the total benefit from sales for this product to the overall benefit
                    $all_benifice += $total_benifice_vente;

                    // Query to get the last value of pu_acheter for this product
                    $achat_query = 'SELECT pu_acheter 
                                    FROM achat 
                                    WHERE id_produit = :id_produit 
                                    ORDER BY date_achat DESC 
                                    LIMIT 1';
                    $achat_stat = $pdo->prepare($achat_query);
                    $achat_stat->execute([':id_produit' => $id_produit]);
                    $achat_result = $achat_stat->fetch(PDO::FETCH_ASSOC);
                    $valeur_par_da = $achat_result['pu_acheter'] ?? 0;

                    // Calculate the value of stock in DA
                    $valeur_stock = $row['qte_stocker'] * $valeur_par_da;
                    // Add this product's stock value to the total stock value in DA
                    $total_valeur_da += $valeur_stock;

                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["nom_produit"]); ?></td>
                        <td><?php echo htmlspecialchars($row["qte_acheter"]); ?></td>
                        <td><?php echo htmlspecialchars($row["qte_vendue"]); ?></td>
                        <td style="background-color: <?php echo ($row['qte_stocker'] < 0) ? '#ff4b4b' : 'transparent'; ?>;">
                            <?php echo htmlspecialchars($row["qte_stocker"]); ?>
                        </td>
                        <td><?php echo number_format($row["benifice_produit"], 2); ?></td>
                        <td><?php echo number_format($total_benifice_vente, 2); ?></td>
                        <td><?php echo number_format($valeur_stock, 2); ?></td>
                    </tr>
                <?php } ?>
                <!-- Row to display the total benefit of all products -->
                <tr>
                    <td colspan="5" style="font-weight: bold; text-align: right;"></td>
                    <td colspan="1" style="font-weight: bold;"><?php echo number_format($all_benifice, 2); ?></td>
                    <td colspan="1" style="font-weight: bold;"><?php echo number_format($total_valeur_da, 2); ?></td>

                </tr>
            </tbody>
        </table>
    <?php } else { ?>
        <h2>La table des produits est vide.</h2>
    <?php } ?>
</body>

</html>