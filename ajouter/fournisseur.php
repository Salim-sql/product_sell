<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include the database connection file
    include_once "../db.inc/connection.php";

    // Get form values
    $nom_fournisseur = $_POST['nom_fournisseur'];
    $num_fournisseur = $_POST['num_fournisseur'];
    $localisation = $_POST['localisation'];

    try {
        // Prepare the INSERT query
        $query = 'INSERT INTO fournisseur (nom_fournisseur, num_fournisseur, localisation) 
                  VALUES (:nom_fournisseur, :num_fournisseur, :localisation)';
        $stmt = $pdo->prepare($query);

        // Bind the parameters
        $stmt->bindParam(':nom_fournisseur', $nom_fournisseur);
        $stmt->bindParam(':num_fournisseur', $num_fournisseur);
        $stmt->bindParam(':localisation', $localisation);

        // Execute the query
        if ($stmt->execute()) {
            echo "<script>alert('Fournisseur ajouté avec succès!');</script>";
            header("Location: ../affichage/fournisseur.php");
            exit();
        } else {
            echo "<script>alert('Erreur lors de l\'ajout du fournisseur');</script>";
        }
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Fournisseur</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-container input[type="text"],
        .form-container input[type="tel"],
        .form-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }

        .form-container a {
            text-decoration: none;
            color: #4CAF50;
            display: block;
            text-align: center;
            margin-top: 15px;
        }

        .form-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>Ajouter Fournisseur</h2>
        <form method="POST" >
            <input type="text" name="nom_fournisseur" placeholder="Nom du Fournisseur" required>
            <input type="tel" name="num_fournisseur" placeholder="Numéro du Fournisseur" required>
            <input type="text" name="localisation" placeholder="Localisation" required>
            <input type="submit" value="Ajouter Fournisseur">
        </form>
        <a href="../affichage/fournisseur.php">Retour à la liste des fournisseurs</a>
    </div>

</body>

</html>