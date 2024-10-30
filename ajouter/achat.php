<?php
// Include your database connection
include_once "../db.inc/connection.php";

// Fetch all fournisseurs
$fournisseur_query = 'SELECT nom_fournisseur FROM fournisseur';
$fournisseur_stat = $pdo->prepare($fournisseur_query);
$fournisseur_stat->execute();
$fournisseurs = $fournisseur_stat->fetchAll(PDO::FETCH_ASSOC);

// Fetch all products from the 'produit' table
$produit_query = 'SELECT id_produit, nom_produit FROM produit';
$produit_stat = $pdo->prepare($produit_query);
$produit_stat->execute();
$produits = $produit_stat->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
    // Sanitize the input data to avoid SQL injection
    $nom_fournisseur = htmlspecialchars($_POST['nom_fournisseur']);
    $id_produit = htmlspecialchars($_POST['id_produit']);
    $qte_acheter = (int)$_POST['qte_acheter'];
    $pu_acheter = (float)$_POST['pu_acheter'];
    $total_achat = (float)$_POST['total_achat'];
    $type_payement = htmlspecialchars($_POST['type_payement']);

    // Initialize versement
    $versement = isset($_POST['versement']) ? (float)$_POST['versement'] : 0;

    // Logic for different payment types
    if ($type_payement === 'credit') {
        $credit = $total_achat;
    } elseif ($type_payement === 'espece') {
        $credit = 0;
    } elseif ($type_payement === 'versement') {
        $credit = $total_achat - $versement;
    }

    try {
        // Prepare the SQL insert query
        $query = "INSERT INTO achat (nom_fournisseur, id_produit, qte_acheter, pu_acheter, total_achat, type_payement, versement,credit) 
                  VALUES (:nom_fournisseur, :id_produit, :qte_acheter, :pu_acheter, :total_achat, :type_payement, :versement,:credit)";

        // Prepare the statement
        $stmt = $pdo->prepare($query);

        // Bind the parameters
        $stmt->bindParam(':nom_fournisseur', $nom_fournisseur);
        $stmt->bindParam(':id_produit', $id_produit);
        $stmt->bindParam(':qte_acheter', $qte_acheter);
        $stmt->bindParam(':pu_acheter', $pu_acheter);
        $stmt->bindParam(':total_achat', $total_achat);
        $stmt->bindParam(':type_payement', $type_payement);
        $stmt->bindParam(':versement', $versement); // Ensure this is set correctly
        $stmt->bindParam(':credit', $credit);
        // Execute the query
        if ($stmt->execute()) {

            // Step 1: Update the product's stock (qte_stocker)
            $update_qte_stocker_query = 'UPDATE produit SET 
        qte_stocker = qte_stocker + :qte_acheter                                     
        WHERE id_produit = :id_produit';
            $update_qte_stocker_stmt = $pdo->prepare($update_qte_stocker_query);
            $update_qte_stocker_stmt->bindParam(':qte_acheter', $qte_acheter);
            $update_qte_stocker_stmt->bindParam(':id_produit', $id_produit);

            // Execute the update query to adjust the stock
            $update_qte_stocker_stmt->execute();

            $update_qte_acheter_query = 'UPDATE produit SET 
        qte_acheter = qte_acheter + :qte_acheter                                     
        WHERE id_produit = :id_produit';
            $update_qte_acheter_stmt = $pdo->prepare($update_qte_acheter_query);
            $update_qte_acheter_stmt->bindParam(':qte_acheter', $qte_acheter);
            $update_qte_acheter_stmt->bindParam(':id_produit', $id_produit);

            // Execute the update query to adjust the stock
            $update_qte_acheter_stmt->execute();
            // Redirect to achat list with success message
            header('Location: ../affichage/achat.php?message=Achat+Added+Successfully');
            exit;
        } else {
            print_r($stmt->errorInfo()); // Show error info
            echo "Erreur lors de l'ajout de l'achat.";
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
    <title>Ajouter Achat</title>
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
        // Function to calculate total achat
        function calculateTotal() {
            var quantity = document.getElementById("qte_acheter").value;
            var unitPrice = document.getElementById("pu_acheter").value;

            // Make sure both fields are filled and valid
            if (quantity && unitPrice && quantity > 0 && unitPrice > 0) {
                var total = parseFloat(quantity) * parseFloat(unitPrice);
                document.getElementById("total_achat").value = total.toFixed(2); // Set total with 2 decimal points
            } else {
                document.getElementById("total_achat").value = ''; // Clear total if inputs are invalid
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
        <h2>Ajouter Achat</h2>

        <!-- Nom du Produit: Dropdown populated from the 'produit' table in the database -->
        <label for="nom_produit">Nom du Produit:</label>
        <select name="id_produit" required>
            <option value="">Sélectionnez un produit</option>
            <?php
            foreach ($produits as $produit) {
                echo '<option value="' . $produit['id_produit'] . '">' . $produit['nom_produit'] . '</option>';
            }
            ?>
        </select>

        <label for="nom_produit">Nom du Fournisseur:</label>
        <select name="nom_fournisseur" required>
            <option value="">Sélectionnez un fournisseur</option>
            <?php
            // Loop through the fetched fournisseurs and create options
            foreach ($fournisseurs as $fournisseur) {
                echo '<option value="' . $fournisseur['nom_fournisseur'] . '">' . $fournisseur['nom_fournisseur'] . '</option>';
            }
            ?>
        </select>

        <label for="qte_acheter">Quantité Achetée:</label>
        <input type="number" name="qte_acheter" id="qte_acheter" oninput="calculateTotal()" required>

        <label for="pu_acheter">Prix Unitaire:</label>
        <input type="number" name="pu_acheter" id="pu_acheter" step="0.01" oninput="calculateTotal()" required>

        <label for="type_payement">Type de Paiement:</label>
        <select name="type_payement" id="type_payement" onchange="toggleMontantVerse()" required>
            <option value="credit">Crédit</option>
            <option value="versement">Versement</option>
            <option value="espece">Espèce</option>
        </select>

        <!-- Hidden input for 'montant versé' that shows only when 'versement' is selected -->
        <div id="versement_div">
            <label for="versement">Montant Versé:</label>
            <input type="number" name="versement" step="0.01">
        </div>

        <label for="total_achat">Total Achat:</label>
        <input type="number" name="total_achat" id="total_achat" step="0.01">



        <input type="submit" name="submit" value="Ajouter Achat">
    </form>
</body>

</html>