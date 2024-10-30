<?php
// Include the database connection file
include_once '../db.inc/connection.php';

try {
    // Fetch all clients from the database
    $query = 'SELECT * FROM client';
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total credit for all clients
    $total_credit_query = 'SELECT SUM(credit) AS total_credit_all_clients FROM vente';
    $total_credit_stmt = $pdo->prepare($total_credit_query);
    $total_credit_stmt->execute();
    $total_credit_result = $total_credit_stmt->fetch(PDO::FETCH_ASSOC);
    $total_credit_all_clients = $total_credit_result['total_credit_all_clients'] ?? 0; // Default to 0 if no credit

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
    <title>Afficher Clients</title>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
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
</head>
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

<button class="add-client-btn"><a href="../ajouter/client.php">Ajouter un nouveau client</a></button>

<h3 style="text-align: center;">Total Crédit de tous les clients: <?php echo number_format($total_credit_all_clients, 2); ?></h3>

<h2>Liste des Clients</h2>

<table>
    <thead>
        <tr>
            <th>Nom du Client</th>
            <th>Numéro de Téléphone</th>
            <th>Localisation</th>
            <th>Crédit Total</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($clients) > 0): ?>
            <?php foreach ($clients as $client): ?>
                <?php
                // Fetch the credit for the current client
                $client_credit_query = 'SELECT SUM(credit) AS total_credit FROM vente WHERE nom_client = :nom_client';
                $client_credit_stmt = $pdo->prepare($client_credit_query);
                $client_credit_stmt->bindParam(':nom_client', $client['nom_client']);
                $client_credit_stmt->execute();
                $client_credit_result = $client_credit_stmt->fetch(PDO::FETCH_ASSOC);
                $client_total_credit = $client_credit_result['total_credit'] ?? 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($client['nom_client']); ?></td>
                    <td><?php echo htmlspecialchars($client['num_client']); ?></td>
                    <td><?php echo htmlspecialchars($client['localisation']); ?></td>
                    <td><?php echo number_format($client_total_credit, 2); ?></td>
                    <td>
                        <a style="color: blue;" href="../modifier/client.php?id=<?php echo $client['nom_client']; ?>">Modifier</a>
                        <a style="color: red;" href="../supprimer/client.php?id=<?php echo $client['nom_client']; ?>" onclick="return confirm('Are you sure you want to delete this client ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">Aucun client trouvé</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>
