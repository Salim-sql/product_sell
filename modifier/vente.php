<?php
// Include your database connection
include_once "../db.inc/connection.php";

// Fetch all clients
$client_query = 'SELECT nom_client FROM client';
$client_stat = $pdo->prepare($client_query);
$client_stat->execute();
$clients = $client_stat->fetchAll(PDO::FETCH_ASSOC);

// Fetch all products
$produit_query = 'SELECT id_produit, nom_produit FROM produit';
$produit_stat = $pdo->prepare($produit_query);
$produit_stat->execute();
$produits = $produit_stat->fetchAll(PDO::FETCH_ASSOC);

// Check if the 'id_vente' is provided in the URL
if (isset($_GET['id'])) {
    $id_vente = (int)$_GET['id'];

    // Fetch the existing sale record from the 'vente' table
    $vente_query = 'SELECT * FROM vente WHERE id_vente = :id_vente';
    $vente_stat = $pdo->prepare($vente_query);
    $vente_stat->bindParam(':id_vente', $id_vente);
    $vente_stat->execute();
    $vente = $vente_stat->fetch(PDO::FETCH_ASSOC);

    // If the sale record doesn't exist, redirect with an error
    if (!$vente) {
        header('Location: ../affichage/vente.php?error=Sale+Not+Found');
        exit;
    }

    // If the form is submitted, process the update
    if (isset($_POST['submit'])) {
        // Sanitize and fetch input values
        $nom_client = htmlspecialchars($_POST['nom_client']);
        $id_produit = htmlspecialchars($_POST['id_produit']);
        $qte_vendue = (int)$_POST['qte_vendue'];
        $pu_vendue = (float)$_POST['pu_vendue'];
        $benifice = (float)$_POST['benifice'];
        $total_vente = (float)$_POST['total_vente'];
        $benifice_total = $benifice * $qte_vendue; // Calculate total profit
        $type_payement = htmlspecialchars($_POST['type_payement']);
        $versement = isset($_POST['versement']) ? (float)$_POST['versement'] : 0;

        // Logic for different payment types
        if ($type_payement === 'credit') {
            $credit = $total_vente;
        } elseif ($type_payement === 'espece') {
            $credit = 0;
        } elseif ($type_payement === 'versement') {
            $credit = $total_vente - $versement;
        }

        try {
            // Update the 'vente' record
            $update_vente_query = "UPDATE vente SET 
                nom_client = :nom_client, 
                id_produit = :id_produit, 
                qte_vendue = :qte_vendue, 
                pu_vendue = :pu_vendue, 
                total_vente = :total_vente, 
                benifice = :benifice, 
                benifice_total = :benifice_total, 
                type_payement = :type_payement, 
                versement = :versement, 
                credit = :credit 
                WHERE id_vente = :id_vente";

            $update_vente_stmt = $pdo->prepare($update_vente_query);
            $update_vente_stmt->bindParam(':nom_client', $nom_client);
            $update_vente_stmt->bindParam(':id_produit', $id_produit);
            $update_vente_stmt->bindParam(':qte_vendue', $qte_vendue);
            $update_vente_stmt->bindParam(':pu_vendue', $pu_vendue);
            $update_vente_stmt->bindParam(':total_vente', $total_vente);
            $update_vente_stmt->bindParam(':benifice', $benifice);
            $update_vente_stmt->bindParam(':benifice_total', $benifice_total);
            $update_vente_stmt->bindParam(':type_payement', $type_payement);
            $update_vente_stmt->bindParam(':versement', $versement);
            $update_vente_stmt->bindParam(':credit', $credit);
            $update_vente_stmt->bindParam(':id_vente', $id_vente);

            if ($update_vente_stmt->execute()) {
                // Step 1: Adjust the stock
                $old_qte_vendue = $vente['qte_vendue']; // Fetch old quantity sold
                $qte_difference = $qte_vendue - $old_qte_vendue; // Calculate difference

                // Update the product's stock accordingly
                $update_qte_stocker_query = 'UPDATE produit SET 
                    qte_stocker = qte_stocker - :qte_difference 
                    WHERE id_produit = :id_produit';
                $update_qte_stocker_stmt = $pdo->prepare($update_qte_stocker_query);
                $update_qte_stocker_stmt->bindParam(':qte_difference', $qte_difference);
                $update_qte_stocker_stmt->bindParam(':id_produit', $id_produit);
                $update_qte_stocker_stmt->execute();

                // Redirect to vente list with success message
                header('Location: ../affichage/vente.php?message=Vente+Updated+Successfully');
                exit;
            } else {
                echo "Erreur lors de la modification de la vente.";
            }
        } catch (PDOException $e) {
            die("Erreur de la base de données: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Modifier Vente</title>
    <style>
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
        form input[type="date"],
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

        /* Add some padding to the form */
        form {
            padding: 30px;
        }
    </style>
    <script>
        // Script for calculating total sale and profit
        function calculateTotal() {
            var quantity = document.getElementById("qte_vendue").value;
            var unitPrice = document.getElementById("pu_vendue").value;
            var benifice = document.getElementById("benifice").value;

            if (quantity && unitPrice && quantity > 0 && unitPrice > 0) {
                var total = parseFloat(quantity) * parseFloat(unitPrice);
                document.getElementById("total_vente").value = total.toFixed(2);

                if (benifice && benifice > 0) {
                    var totalBenifice = parseFloat(benifice) * parseFloat(quantity);
                    document.getElementById("benifice_total").value = totalBenifice.toFixed(2);
                } else {
                    document.getElementById("benifice_total").value = '';
                }
            } else {
                document.getElementById("total_vente").value = '';
                document.getElementById("benifice_total").value = '';
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
        <h2>Modifier Vente</h2>

        <label for="nom_produit">Nom du Produit:</label>
        <select name="id_produit" required>
            <?php
            foreach ($produits as $produit) {
                $selected = $produit['id_produit'] == $vente['id_produit'] ? 'selected' : '';
                echo '<option value="' . $produit['id_produit'] . '" ' . $selected . '>' . $produit['nom_produit'] . '</option>';
            }
            ?>
        </select>

        <label for="nom_client">Nom du Client:</label>
        <select name="nom_client" required>
            <?php
            foreach ($clients as $client) {
                $selected = $client['nom_client'] == $vente['nom_client'] ? 'selected' : '';
                echo '<option value="' . $client['nom_client'] . '" ' . $selected . '>' . $client['nom_client'] . '</option>';
            }
            ?>
        </select>

        <label for="qte_vendue">Quantité Vendue:</label>
        <input type="number" name="qte_vendue" id="qte_vendue" value="<?php echo $vente['qte_vendue']; ?>" oninput="calculateTotal()" required>

        <label for="pu_vendue">Prix Unitaire:</label>
        <input type="number" name="pu_vendue" id="pu_vendue" value="<?php echo $vente['pu_vendue']; ?>" step="0.01" oninput="calculateTotal()" required>

        <label for="benifice">Bénéfice Unitaire:</label>
        <input type="number" name="benifice" id="benifice" value="<?php echo $vente['benifice']; ?>" step="0.01" oninput="calculateTotal()" required>

        <label for="benifice_total">Bénéfice Total:</label>
        <input type="number" name="benifice_total" id="benifice_total" value="<?php echo $vente['benifice_total']; ?>" step="0.01" readonly>

        <label for="type_payement">Type de Paiement:</label>
        <select name="type_payement" id="type_payement" onchange="toggleMontantVerse()" required>
            <option value="credit" <?php echo $vente['type_payement'] === 'credit' ? 'selected' : ''; ?>>Crédit</option>
            <option value="versement" <?php echo $vente['type_payement'] === 'versement' ? 'selected' : ''; ?>>Versement</option>
            <option value="espece" <?php echo $vente['type_payement'] === 'espece' ? 'selected' : ''; ?>>Espèce</option>
        </select>

        <div id="versement_div" style="display: <?php echo $vente['type_payement'] === 'versement' ? 'block' : 'none'; ?>;">
            <label for="versement">Montant Versé:</label>
            <input type="number" name="versement" value="<?php echo $vente['versement']; ?>" step="0.01">
        </div>

        <label for="total_vente">Total Vente:</label>
        <input type="number" name="total_vente" id="total_vente" value="<?php echo $vente['total_vente']; ?>" step="0.01" readonly>

        <input type="submit" name="submit" value="Modifier Vente">
    </form>
</body>

</html>