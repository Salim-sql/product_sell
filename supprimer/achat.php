<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Check if 'id' is set in the URL (id_achat)
if (isset($_GET['id'])) {
    // Get the id_achat from the URL
    $id_achat = $_GET['id'];

    // Step 1: Retrieve the details of the achat (product and quantity bought)
    $query = 'SELECT id_produit, qte_acheter FROM achat WHERE id_achat = :id_achat';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_achat', $id_achat);
    $stmt->execute();
    $achat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($achat) {
        $id_produit = $achat['id_produit'];
        $qte_acheter = $achat['qte_acheter'];

        // Step 2: Update the produit table by adding back the quantity to qte_acheter_produit and qte_stocker
        $update_produit_query = 'UPDATE produit 
                                 SET qte_acheter = qte_acheter - :qte_acheter,
                                     qte_stocker = qte_stocker - :qte_acheter
                                 WHERE id_produit = :id_produit';
        $update_produit_stmt = $pdo->prepare($update_produit_query);
        $update_produit_stmt->bindParam(':qte_acheter', $qte_acheter);
        $update_produit_stmt->bindParam(':id_produit', $id_produit);

        // Execute the update query to adjust the product quantities
        if ($update_produit_stmt->execute()) {
            // Step 3: Delete the achat from the database
            $delete_query = 'DELETE FROM achat WHERE id_achat = :id_achat';
            $delete_stmt = $pdo->prepare($delete_query);
            $delete_stmt->bindParam(':id_achat', $id_achat);

            // Execute the deletion query
            if ($delete_stmt->execute()) {
                // Redirect back to the achat list with a success message
                header('Location: ../affichage/achat.php?message=Achat+Deleted+Successfully');
                exit;
            } else {
                echo "Error deleting achat!";
            }
        } else {
            echo "Error updating product quantities!";
        }
    } else {
        echo "Achat not found!";
    }
} else {
    echo "Invalid request!";
    exit;
}
