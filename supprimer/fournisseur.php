<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Check if 'id' is set in the URL
if (isset($_GET['id'])) {
    // Get the nom_fournisseur (or id depending on your schema) from the URL
    $nom_fournisseur = $_GET['id'];

    // Prepare the DELETE statement
    $query = 'DELETE FROM fournisseur WHERE nom_fournisseur = :nom_fournisseur';
    $stmt = $pdo->prepare($query);

    // Bind the parameter
    $stmt->bindParam(':nom_fournisseur', $nom_fournisseur);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect back to the fournisseur list or show a success message
        header('Location: ../affichage/fournisseur.php?message=fournisseur+Deleted+Successfully');
        exit;
    } else {
        echo "Error deleting record!";
    }
} else {
    echo "Invalid request!";
    exit;
}
