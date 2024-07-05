<?php
// Include database connection file
include 'connection.php';

// Query to fetch records from ot table and update attendance table
$sql = "SELECT ot.employee_id, ot.overtime, ot.status, attendance.employee_id AS att_employee_id, attendance.date AS att_date
        FROM ot
        INNER JOIN attendance ON ot.employee_id = attendance.employee_id AND DATE_FORMAT(ot.overtime, '%Y-%m-%d') = attendance.date
        WHERE ot.status = 'Accepted'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employee_id = $row['employee_id'];
        $overtime = $row['overtime'];
        $status = $row['status'];
        $att_employee_id = $row['att_employee_id'];
        $att_date = $row['att_date'];

        // Extract date from overtime (assuming it is in format 'YYYY-MM-DD HH:mm:ss')
        $overtime_date = substr($overtime, 0, 10); // Extracts 'YYYY-MM-DD'

        // Check if status is 'Accepted' and employee_id and date match between ot and attendance tables
        if ($status === 'Accepted' && $employee_id === $att_employee_id && $overtime_date === $att_date) {
            // Extract hours from overtime (assuming it follows the date in format 'YYYY-MM-DD HH:mm:ss')
            // Example: '2024-06-23 05:30:00'
            $overtime_hours = substr($overtime, 11); // Extracts '05:30:00'

            // Update attendance table with overtime hours
            $update_sql = "UPDATE attendance SET overtime = ? WHERE employee_id = ? AND date = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $overtime_hours, $employee_id, $att_date);

            if ($update_stmt->execute()) {
                echo "Overtime hours updated successfully for Employee ID: $employee_id, Date: $att_date<br>";
            } else {
                echo "Error updating overtime hours: " . $conn->error . "<br>";
            }
        } else {
            if ($status !== 'Accepted') {
                echo "Overtime request is not accepted for Employee ID: $employee_id, Date: $overtime_date<br>";
            } else {
                echo "No matching records found between ot and attendance tables for Employee ID: $employee_id, Date: $overtime_date<br>";
            }
        }
    }
} else {
    echo "No matching records found between ot and attendance tables.";
}

$update_stmt->close();
$conn->close();
?>
