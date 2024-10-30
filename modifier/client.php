<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Check if 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    // Get the client ID from the URL
    $nom_client = $_GET['id'];

    // Fetch the existing client details
    $query = 'SELECT * FROM client WHERE nom_client = :nom_client';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nom_client', $nom_client);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no client is found, exit with an error message
    if (!$client) {
        echo "Client not found!";
        exit;
    }

    // Handle form submission to update client details
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom_client = $_POST['nom_client'];
        $num_client = $_POST['num_client'];
        $localisation = $_POST['localisation'];

        // Update client in the database
        $update_query = 'UPDATE client SET 
                        nom_client = :nom_client,
                        num_client = :num_client,
                        localisation = :localisation
                        WHERE nom_client = :nom_client';
        $update_stmt = $pdo->prepare($update_query);

        // Bind parameters
        $update_stmt->bindParam(':nom_client', $nom_client);
        $update_stmt->bindParam(':num_client', $num_client);
        $update_stmt->bindParam(':localisation', $localisation);

        // Execute the update
        if ($update_stmt->execute()) {
            echo "Client updated successfully!";
            header("location:../affichage/client.php");
            exit();
        } else {
            echo "Failed to update client!";
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
    <title>Modifier Client</title>
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
        input[type="number"] {
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
    </style>
</head>

<body>
    <form method="POST">
        <h2>Modifier Client</h2>

        <label for="nom_client">Nom du Client:</label>
        <input type="text" name="nom_client" value="<?php echo htmlspecialchars($client['nom_client']); ?>" required>

        <label for="num_client">Numéro de Téléphone:</label>
        <input type="text" name="num_client" value="<?php echo htmlspecialchars($client['num_client']); ?>" required>

        <label for="localisation">Localisation:</label>
        <input type="text" name="localisation" value="<?php echo htmlspecialchars($client['localisation']); ?>" required>

        <button type="submit">Modifier le client</button>
        <button type="button"><a href="afficher_client.php">Annuler</a></button>
    </form>
</body>

</html>