<?php
// Include your database connection
include_once "../db.inc/connection.php";

// Fetch all clients
$client_query = 'SELECT nom_client FROM client';
$client_stat = $pdo->prepare($client_query);
$client_stat->execute();
$clients = $client_stat->fetchAll(PDO::FETCH_ASSOC);

// Fetch all products from the 'produit' table
$produit_query = 'SELECT id_produit, nom_produit FROM produit'; // Assuming 'prix_achat' is the cost price
$produit_stat = $pdo->prepare($produit_query);
$produit_stat->execute();
$produits = $produit_stat->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
    // Sanitize the input data to avoid SQL injection
    $nom_client = htmlspecialchars($_POST['nom_client']);
    $id_produit = htmlspecialchars($_POST['id_produit']);
    $qte_vendue = (int)$_POST['qte_vendue'];
    $pu_vendue = (float)$_POST['pu_vendue'];
    $total_vente = (float)$_POST['total_vente'];
    $type_payement = htmlspecialchars($_POST['type_payement']);
    $versement = isset($_POST['versement']) ? (float)$_POST['versement'] : 0;
    $benifice = (float)$_POST['benifice']; // Input for profit per unit

    // Calculate benifice_total based on quantity sold
    $benifice_total = $benifice * $qte_vendue; // Total profit = profit per unit * quantity sold

    // Calculate credit based on payment type
    if ($type_payement === 'credit') {
        $credit = $total_vente;
    } elseif ($type_payement === 'espece') {
        $credit = 0;
    } elseif ($type_payement === 'versement') {
        $credit = $total_vente - $versement;
    }

    try {
        // Prepare the SQL insert query
        $query = "INSERT INTO vente (nom_client, id_produit, qte_vendue, pu_vendue, total_vente, type_payement, versement, credit, benifice, benifice_total) 
                  VALUES (:nom_client, :id_produit, :qte_vendue, :pu_vendue, :total_vente, :type_payement, :versement, :credit, :benifice, :benifice_total)";

        // Prepare the statement
        $stmt = $pdo->prepare($query);

        // Bind the parameters
        $stmt->bindParam(':nom_client', $nom_client);
        $stmt->bindParam(':id_produit', $id_produit);
        $stmt->bindParam(':qte_vendue', $qte_vendue);
        $stmt->bindParam(':pu_vendue', $pu_vendue);
        $stmt->bindParam(':total_vente', $total_vente);
        $stmt->bindParam(':type_payement', $type_payement);
        $stmt->bindParam(':versement', $versement);
        $stmt->bindParam(':credit', $credit);
        $stmt->bindParam(':benifice', $benifice);
        $stmt->bindParam(':benifice_total', $benifice_total);

        // Execute the query
        if ($stmt->execute()) {
            // Step 1: Update the product's stock (qte_stocker)
            $update_qte_stocker_query = 'UPDATE produit SET 
                qte_stocker = qte_stocker - :qte_vendue                                     
                WHERE id_produit = :id_produit';
            $update_qte_stocker_stmt = $pdo->prepare($update_qte_stocker_query);
            $update_qte_stocker_stmt->bindParam(':qte_vendue', $qte_vendue);
            $update_qte_stocker_stmt->bindParam(':id_produit', $id_produit);

            // Execute the update query to adjust the stock
            $update_qte_stocker_stmt->execute();

            // Step 2: Update the product's stock (qte_vendue)
            $update_qte_vendue_query = 'UPDATE produit SET 
                qte_vendue = qte_vendue + :qte_vendue                                     
                WHERE id_produit = :id_produit';
            $update_qte_vendue_stmt = $pdo->prepare($update_qte_vendue_query);
            $update_qte_vendue_stmt->bindParam(':qte_vendue', $qte_vendue);
            $update_qte_vendue_stmt->bindParam(':id_produit', $id_produit);

            // Execute the update query to adjust the stock
            $update_qte_vendue_stmt->execute();
            // Redirect to vente list with success message
            header('Location: ../affichage/vente.php?message=Vente+Added+Successfully');
            exit;
        } else {
            print_r($stmt->errorInfo()); // Show error info
            echo "Erreur lors de l'ajout de la vente.";
        }
    } catch (PDOException $e) {
        die("Erreur de la base de données: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Ajouter Vente</title>
    <style>
        /* Add your CSS styling here (same as before) */
        /* Form container */
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            margin: 15vh auto;
        }

        /* Form header */
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
            font-size: 24px;
        }

        /* Label styling */
        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        /* Input fields styling */
        form input[type="text"],
        form input[type="number"],
        form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
            box-sizing: border-box;
        }

        /* Styling for buttons and submit */
        form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Button hover effect */
        form input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Hidden input div */
        #versement_div {
            display: none;
        }
    </style>
    <script>
        // Function to calculate total vente and benifice total
        function calculateTotal() {
            var quantity = document.getElementById("qte_vendue").value;
            var unitPrice = document.getElementById("pu_vendue").value;
            var benifice = document.getElementById("benifice").value;

            // Make sure both fields are filled and valid
            if (quantity && unitPrice && quantity > 0 && unitPrice > 0) {
                var total = parseFloat(quantity) * parseFloat(unitPrice);
                document.getElementById("total_vente").value = total.toFixed(2); // Set total with 2 decimal points

                // Calculate benifice_total
                if (benifice && benifice > 0) {
                    var benificeTotal = parseFloat(benifice) * parseInt(quantity);
                    document.getElementById("benifice_total").value = benificeTotal.toFixed(2);
                } else {
                    document.getElementById("benifice_total").value = ''; // Clear total if benifice is invalid
                }
            } else {
                document.getElementById("total_vente").value = ''; // Clear total if inputs are invalid
                document.getElementById("benifice_total").value = ''; // Clear benifice_total
            }
        }

        function toggleMontantVerse() {
            var paymentType = document.getElementById("type_payement").value;
            var montantVerseDiv = document.getElementById("versement_div");

            if (paymentType === "versement") {
                montantVerseDiv.style.display = "block";
            } else {
                montantVerseDiv.style.display = "none";
            }
        }
    </script>
</head>

<body>
    <form method="post">
        <h2>Ajouter Vente</h2>

        <!-- Nom du Produit: Dropdown populated from the 'produit' table in the database -->
        <label for="nom_produit">Nom du Produit:</label>
        <select name="id_produit" id="id_produit" onchange="calculateTotal()" required>
            <option value="">Sélectionnez un produit</option>
            <?php
            foreach ($produits as $produit) {
                echo '<option value="' . $produit['id_produit'] . '">' . $produit['nom_produit'] . '</option>';
            }
            ?>
        </select>

        <label for="nom_client">Nom du Client:</label>
        <select name="nom_client" required>
            <option value="">Sélectionnez un client</option>
            <?php
            foreach ($clients as $client) {
                echo '<option value="' . htmlspecialchars($client['nom_client']) . '">' . htmlspecialchars($client['nom_client']) . '</option>';
            }
            ?>
        </select>

        <label for="qte_vendue">Quantité Vendue:</label>
        <input type="number" name="qte_vendue" id="qte_vendue" min="1" required onchange="calculateTotal()">

        <label for="pu_vendue">Prix Unitaire Vendue:</label>
        <input type="number" name="pu_vendue" id="pu_vendue" step="0.01" required onchange="calculateTotal()">

        <label for="total_vente">Total Vente:</label>
        <input type="number" name="total_vente" id="total_vente" step="0.01" readonly>

        <label for="benifice">Benifice par Unité:</label>
        <input type="number" name="benifice" id="benifice" step="0.01" required onchange="calculateTotal()">

        <label for="benifice_total">Benifice Total:</label>
        <input type="number" name="benifice_total" id="benifice_total" step="0.01" readonly>

        <label for="type_payement">Type de Paiement:</label>
        <select name="type_payement" id="type_payement" onchange="toggleMontantVerse()" required>
            <option value="">Sélectionnez un type de paiement</option>
            <option value="espece">Espèce</option>
            <option value="credit">Crédit</option>
            <option value="versement">Versement</option>
        </select>

        <div id="versement_div">
            <label for="versement">Montant Verse:</label>
            <input type="number" name="versement" id="versement" step="0.01" onchange="calculateTotal()">
        </div>

        <input type="submit" name="submit" value="Ajouter Vente">
    </form>
</body>
</html>
