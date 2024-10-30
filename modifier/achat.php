<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    // Get the id of the achat to modify
    $id_achat = $_GET['id'];

    // Fetch the existing data of the achat
    $query = 'SELECT * FROM achat WHERE id_achat = :id_achat';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_achat', $id_achat);
    $stmt->execute();
    $achat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$achat) {
        echo "achat not found!";
        exit;
    }

    // Retrieve the ancien_qte_acheter from the achat
    $ancien_qte_acheter = $achat['qte_acheter'];
    // Handle form submission to update the achat
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_produit = $_POST['id_produit'];
        $nouvelle_qte_acheter = $_POST['qte_acheter'];
        $pu_acheter = $_POST['pu_acheter'];
        $total_achat = $_POST['total_achat'];
        $versement = isset($_POST['versement']) ? $_POST['versement'] : 0; // Ensure versement is always set
        $type_payement = $_POST['type_payement'];

        // Logic for different payment types
        if ($type_payement === 'credit') {
            $credit = $total_achat;
        } elseif ($type_payement === 'espece') {
            $credit = 0;
        } elseif ($type_payement === 'versement') {
            $credit = $total_achat - $versement;
        }

        // Update the achat in the database
        $update_query = 'UPDATE achat SET 
                        id_produit = :id_produit,
                        qte_acheter = :nouvelle_qte_acheter,
                        pu_acheter = :pu_acheter,
                        total_achat = :total_achat,
                        versement = :versement,
                        credit = :credit,
                        type_payement = :type_payement
                        WHERE id_achat = :id_achat';
        $update_stmt = $pdo->prepare($update_query);

        // Bind the parameters
        $update_stmt->bindParam(':id_produit', $id_produit);
        $update_stmt->bindParam(':nouvelle_qte_acheter', $nouvelle_qte_acheter);
        $update_stmt->bindParam(':pu_acheter', $pu_acheter);
        $update_stmt->bindParam(':total_achat', $total_achat);
        $update_stmt->bindParam(':versement', $versement);
        $update_stmt->bindParam(':credit', $credit);
        $update_stmt->bindParam(':type_payement', $type_payement);
        $update_stmt->bindParam(':id_achat', $id_achat);

        // Execute the update
        if ($update_stmt->execute()) {
            // Step 1: Update the product's stock (qte_stocker)
            $update_qte_stocker_query = 'UPDATE produit SET 
                qte_stocker = (qte_stocker - :ancien_qte_acheter) + :nouvelle_qte_acheter
                WHERE id_produit = :id_produit';
            $update_qte_stocker_stmt = $pdo->prepare($update_qte_stocker_query);
            $update_qte_stocker_stmt->bindParam(':ancien_qte_acheter', $ancien_qte_acheter);
            $update_qte_stocker_stmt->bindParam(':nouvelle_qte_acheter', $nouvelle_qte_acheter);
            $update_qte_stocker_stmt->bindParam(':id_produit', $id_produit);

            // Execute the update query to adjust the stock
            $update_qte_stocker_stmt->execute();

            // Step 2: Update qte_acheter in the produit table
            $update_qte_acheter_query = 'UPDATE produit SET 
                qte_acheter = (qte_acheter - :ancien_qte_acheter) + :nouvelle_qte_acheter
                WHERE id_produit = :id_produit';

            $update_qte_acheter_stmt = $pdo->prepare($update_qte_acheter_query);
            $update_qte_acheter_stmt->bindParam(':ancien_qte_acheter', $ancien_qte_acheter);
            $update_qte_acheter_stmt->bindParam(':nouvelle_qte_acheter', $nouvelle_qte_acheter);
            $update_qte_acheter_stmt->bindParam(':id_produit', $id_produit);

            // Execute the update query to adjust qte_acheter
            $update_qte_acheter_stmt->execute();

            echo "Achat updated successfully!";
            header("location:../affichage/achat.php");
            exit();
        } else {
            echo "Failed to update achat!";
        }
    }
} else {
    echo "Invalid request.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Modify achat</title>
    <style>
        form {
            max-width: 400px;
            margin: 15vh auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        button[type="button"] {
            background-color: #007bff;
        }

        button[type="button"] a {
            color: white;
            text-decoration: none;
        }

        #versement_div {
            display: none;
        }
    </style>
    <script>
        function calculateTotal() {
            var quantity = document.getElementById("qte_acheter").value;
            var unitPrice = document.getElementById("pu_acheter").value;

            if (quantity && unitPrice && quantity > 0 && unitPrice > 0) {
                var total = parseFloat(quantity) * parseFloat(unitPrice);
                document.getElementById("total_achat").value = total.toFixed(2);
            } else {
                document.getElementById("total_achat").value = '';
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
    <form method="POST">
        <h2>Modifier Achat</h2>

        <label for="id_produit">Nom du Produit:</label>
        <input type="text" name="id_produit" value="<?php echo htmlspecialchars($achat['id_produit']); ?>" required>

        <label for="qte_acheter">Quantité Achetée:</label>
        <input type="number" name="qte_acheter" id="qte_acheter" value="<?php echo htmlspecialchars($achat['qte_acheter']); ?>" oninput="calculateTotal()" required>

        <label for="pu_acheter">Prix Unitaire:</label>
        <input type="number" name="pu_acheter" id="pu_acheter" step="0.01" value="<?php echo htmlspecialchars($achat['pu_acheter']); ?>" oninput="calculateTotal()" required>

        <label for="total_achat">Total:</label>
        <input type="number" name="total_achat" id="total_achat" step="0.01" value="<?php echo htmlspecialchars($achat['total_achat']); ?>" readonly>

        <label for="type_payement">Type de Paiement:</label>
        <select name="type_payement" id="type_payement" onchange="toggleMontantVerse()" required>
            <option value="espece" <?php if ($achat['type_payement'] == 'espece') echo 'selected'; ?>>Espèce</option>
            <option value="credit" <?php if ($achat['type_payement'] == 'credit') echo 'selected'; ?>>Crédit</option>
            <option value="versement" <?php if ($achat['type_payement'] == 'versement') echo 'selected'; ?>>Versement</option>
        </select>

        <div id="versement_div" <?php if ($achat['type_payement'] != 'versement') echo 'style="display:none;"'; ?>>
            <label for="versement">Montant Versé:</label>
            <input type="number" name="versement" id="versement" step="0.01" value="<?php echo htmlspecialchars($achat['versement']); ?>">
        </div>

        <button type="submit">Modifier l'achat</button>
        <button type="button"><a href="../affichage/achat.php">Annuler</a></button>
    </form>
</body>

</html>