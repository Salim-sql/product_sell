<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Check if 'id' is set in the URL
if (isset($_GET['id'])) {
    // Get the id_vente from the URL
    $id_vente = $_GET['id'];

    // Step 1: Fetch the existing data of the vente to get qte_vendue and benifice_total
    $query = 'SELECT qte_vendue, benifice_total, id_produit FROM vente WHERE id_vente = :id_vente';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_vente', $id_vente);
    $stmt->execute();
    $vente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vente) {
        echo "Vente not found!";
        exit;
    }

    // Get the quantity sold and total profit from the fetched vente
    $qte_vendue = $vente['qte_vendue'];
    $benifice_total = $vente['benifice_total'];
    $id_produit = $vente['id_produit'];

    // Step 2: Prepare the DELETE statement for vente
    $delete_query = 'DELETE FROM vente WHERE id_vente = :id_vente';
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->bindParam(':id_vente', $id_vente);

    // Step 3: Execute the delete statement
    if ($delete_stmt->execute()) {
        // Step 4: Update the produit table
        $update_produit_query = 'UPDATE produit SET 
                                    qte_vendue = qte_vendue - :qte_vendue,
                                    benifice_produit = benifice_produit - :benifice_total
                                 WHERE id_produit = :id_produit';

        $update_produit_stmt = $pdo->prepare($update_produit_query);
        // Bind parameters
        $update_produit_stmt->bindParam(':qte_vendue', $qte_vendue);
        $update_produit_stmt->bindParam(':benifice_total', $benifice_total);
        $update_produit_stmt->bindParam(':id_produit', $id_produit);

        // Execute the update for produit
        if ($update_produit_stmt->execute()) {
            // Redirect back to the vente list or show a success message
            header('Location: ../affichage/vente.php?message=Record+Deleted+Successfully');
            exit;
        } else {
            echo "Error updating product record!";
        }
    } else {
        echo "Error deleting record!";
    }
} else {
    echo "Invalid request!";
    exit;
}
