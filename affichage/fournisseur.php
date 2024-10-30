<?php
try {
    include_once "../db.inc/connection.php";

    // Fetch all fournisseurs
    $query = 'SELECT * FROM fournisseur';
    $stat = $pdo->prepare($query);
    $stat->execute();
    $result = $stat->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the total credit for all clients
    $total_credit_query = 'SELECT SUM(credit) AS total_credit_all FROM achat';
    $total_credit_stat = $pdo->prepare($total_credit_query);
    $total_credit_stat->execute();
    $total_credit_result = $total_credit_stat->fetch(PDO::FETCH_ASSOC);
    $total_credit_all_clients = $total_credit_result['total_credit_all'] ?? 0;  // If no credit, default to 0

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
    <title>Fournisseur et Achats</title>
</head>
<style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-client-btn {
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            text-align: center;
        }

        .add-client-btn a {
            color: white;
            text-decoration: none;
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

    <button class="add-client-btn"><a href="../ajouter/fournisseur.php">Ajouter un nouveau Fournisseur</a></button>

    <br><br>
    <!-- Step 2: Display the total credit for all clients -->
    <h3 style="text-align: center;">Total Crédit de tous les clients: <?php echo number_format($total_credit_all_clients, 2); ?> </h3>

    <?php
    if (!empty($result)) {
    ?>
        <h2 style="text-align: center; margin-top:100px;">Les fournisseurs</h2>

        <table>
        <thead>
                        <tr>
                            <th>Nom du fournisseur</th>
                            <th>Numéro fournisseur</th>
                            <th>Localisation</th>
                            <th>Total Crédit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

            <tbody>
                <?php
                foreach ($result as $row) {
                    $nom_fournisseur = $row["nom_fournisseur"];

                    // Query to sum all credits for this fournisseur
                    $credit_query = 'SELECT SUM(credit) AS total_credit FROM achat WHERE nom_fournisseur = :nom_fournisseur';
                    $credit_stat = $pdo->prepare($credit_query);
                    $credit_stat->bindParam(':nom_fournisseur', $nom_fournisseur);
                    $credit_stat->execute();
                    $credit_result = $credit_stat->fetch(PDO::FETCH_ASSOC);
                    $total_credit = $credit_result['total_credit'] ?? 0;
                ?>
                   
                    <tr>
                        <td><?php echo $nom_fournisseur; ?></td>
                        <td><?php echo $row["num_fournisseur"]; ?></td>
                        <td><?php echo $row["localisation"]; ?></td>
                        <td><?php echo number_format($total_credit, 2); ?> </td>
                        <td>
                            <a style="color: blue;" href="../modifier/fournisseur.php?id=<?php echo $row['nom_fournisseur']; ?>">Modifier</a> |
                            <a style="color: red;" href="../supprimer/fournisseur.php?id=<?php echo $row['nom_fournisseur']; ?>" onclick="return confirm('Are you sure you want to delete this fournisseur?');">Supprimer</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>


    <?php
    } else {
        echo "<h2>La table fournisseur est vide</h2>";
    }
    ?>
</body>

</html>