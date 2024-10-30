<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Check if 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    // Get the client ID from the URL
    $nom_client = $_GET['id'];

    // Prepare the DELETE query
    $delete_query = 'DELETE FROM client WHERE nom_client = :nom_client';
    $stmt = $pdo->prepare($delete_query);

    // Bind the client ID to the query
    $stmt->bindParam(':nom_client', $nom_client);

    // Execute the delete query
    if ($stmt->execute()) {
        echo "Client deleted successfully!";
        header("Location: ../affichage/client.php");
        exit();
    } else {
        echo "Failed to delete client!";
    }
} else {
    echo "Invalid request.";
    exit;
}
