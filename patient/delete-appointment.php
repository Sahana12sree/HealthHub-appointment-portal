<?php

session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
}


if ($_GET) {
    // Import database connection
    include("../connection.php");

    $id = $_GET["id"];

    // Check if appointment is within cancellation window
    $currentTime = time();  // Get current timestamp in seconds
    $sqlWindow = "SELECT appointmenttime FROM appointment WHERE appoid = ?";
    $stmtWindow = $database->prepare($sqlWindow);
    $stmtWindow->bind_param("i", $id);
    $stmtWindow->execute();
    $resultWindow = $stmtWindow->get_result();
    $rowWindow = $resultWindow->fetch_assoc();

    if ($rowWindow) {
        $appointmentTime = strtotime($rowWindow['appointmenttime']);  // Convert appointment time to timestamp
        $cancellationWindow = 5 * 60 * 60; 
        if ($currentTime - $appointmentTime < $cancellationWindow) {
            
            echo "This appointment cannot be cancelled as it is within the 5-hour window.";
        } else {
            // Appointment is outside cancellation window - proceed with deletion
            $sql = "DELETE FROM appointment WHERE appoid = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            header("location: appointment.php");
        }
    } else {
        // Handle case where appointment ID is not found
        echo "Appointment not found.";
    }
}

?>
