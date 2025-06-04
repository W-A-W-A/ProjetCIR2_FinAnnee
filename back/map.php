<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include the database connection (reuse it across all APIs)
require_once __DIR__ . '/../back/db.php';

try {
    // Get filter parameters from GET request
    $department_filter = isset($_GET['department']) ? $_GET['department'] : null;
    $region_filter = isset($_GET['region']) ? $_GET['region'] : null;
    $year_filter = isset($_GET['year']) ? $_GET['year'] : null;

    // Base SQL query with all necessary joins
    $sql = "SELECT 
                i.id,
                i.lat,
                i.lon,
                i.an_installation as year,
                i.puissance_crete as power,
                d.dep_nom as department_name,
                d.id as department_code,
                r.dep_reg as region_name,
                r.id as region_code
            FROM Installation i
            LEFT JOIN Departement d ON i.code_insee = d.id
            LEFT JOIN Region r ON d.id_reg = r.id
            WHERE 1=1";
    
    $params = [];
    $types = "";

    // Add filters if provided
    if ($department_filter && $department_filter !== 'all') {
        $sql .= " AND d.id = ?";
        $params[] = $department_filter;
        $types .= "s";
    }

    if ($region_filter && $region_filter !== 'all') {
        $sql .= " AND r.id = ?";
        $params[] = $region_filter;
        $types .= "s";
    }

    if ($year_filter && $year_filter !== 'all') {
        $sql .= " AND i.an_installation = ?";
        $params[] = intval($year_filter);
        $types .= "i";
    }

    // Order by year and power for consistent results
    $sql .= " ORDER BY i.an_installation DESC, i.puissance_crete DESC";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transform data to match the required format
    $solarInstallations = [];
    
    foreach ($results as $row) {
        // Format power with unit
        $power_formatted = $row['power'] ? $row['power'] . ' kW' : 'N/A';
        
        // Create city name (you might need to adjust this based on your data)
        // Since I don't see a city field in your schema, using department name as fallback
        $city = $row['department_name'] ?: 'Unknown';
        
        $installation = [
            'lat' => floatval($row['lat']),
            'lng' => floatval($row['lon']),
            'city' => $city,
            'year' => intval($row['year']),
            'department' => $row['department_code'],
            'department_name' => $row['department_name'],
            'region' => $row['region_code'],
            'region_name' => $row['region_name'],
            'power' => $power_formatted,
            'power_numeric' => floatval($row['power'])
        ];
        
        $solarInstallations[] = $installation;
    }

    // Return the data
    echo json_encode([
        'success' => true,
        'data' => $solarInstallations,
        'count' => count($solarInstallations),
        'filters' => [
            'department' => $department_filter,
            'region' => $region_filter,
            'year' => $year_filter
        ]
    ]);

} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Handle other errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
