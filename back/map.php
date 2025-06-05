<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include the database connection
require_once __DIR__ . '/db.php';

try {
    // Check if this is a request for filter options
    $action = isset($_GET['action']) ? $_GET['action'] : 'installations';
    
    if ($action === 'get_years') {
        // Get 20 random years that have installations
        $sql = "SELECT DISTINCT i.an_installation as year 
                FROM Installation i 
                WHERE i.an_installation IS NOT NULL 
                AND i.an_installation > 0
                ORDER BY RAND() 
                LIMIT 20";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Sort years in descending order for better UX
        rsort($results);
        
        echo json_encode([
            'success' => true,
            'data' => array_map('intval', $results),
            'count' => count($results)
        ]);
        exit;
    }
    
    if ($action === 'get_departments') {
        // Get 20 random departments that have installations
        // Fixed: removed dep_code which doesn't exist in the schema
        $sql = "SELECT DISTINCT d.id, d.dep_nom as name
                FROM Departement d
                INNER JOIN Commune c ON c.id_Departement = d.id
                INNER JOIN Installation i ON i.id_Commune = c.id
                WHERE d.dep_nom IS NOT NULL 
                AND d.dep_nom != ''
                ORDER BY RAND() 
                LIMIT 20";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the results
        $departments = [];
        foreach ($results as $row) {
            $departments[] = [
                'id' => intval($row['id']),
                'name' => $row['name'],
                'code' => sprintf('%02d', $row['id']) // Use ID as code since dep_code doesn't exist
            ];
        }
        
        // Sort departments by name for better UX
        usort($departments, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        echo json_encode([
            'success' => true,
            'data' => $departments,
            'count' => count($departments)
        ]);
        exit;
    }

    // Default action: get installations
    // Get filter parameters from GET request
    $department_filter = isset($_GET['department']) ? $_GET['department'] : null;
    $region_filter = isset($_GET['region']) ? $_GET['region'] : null;
    $year_filter = isset($_GET['year']) ? $_GET['year'] : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;

    // Base SQL query with correct joins based on your schema
    $sql = "SELECT 
                i.id,
                i.lat,
                i.lon,
                i.an_installation as year,
                i.puissance_crete as power,
                d.dep_nom as department_name,
                d.id as department_code,
                r.dep_reg as region_name,
                r.id as region_code,
                c.com_nom as city_name,
                c.code_insee,
                i.code_postal
            FROM Installation i
            LEFT JOIN Commune c ON i.id_Commune = c.id
            LEFT JOIN Departement d ON c.id_Departement = d.id
            LEFT JOIN Region r ON d.id_Region = r.id
            WHERE i.lat IS NOT NULL 
            AND i.lon IS NOT NULL 
            AND i.lat != 0 
            AND i.lon != 0";
    
    $params = [];

    // Add filters if provided
    if ($department_filter && $department_filter !== 'all' && $department_filter !== '') {
        $sql .= " AND d.id = ?";
        $params[] = intval($department_filter);
    }

    if ($region_filter && $region_filter !== 'all' && $region_filter !== '') {
        $sql .= " AND r.id = ?";
        $params[] = intval($region_filter);
    }

    if ($year_filter && $year_filter !== 'all' && $year_filter !== '') {
        $sql .= " AND i.an_installation = ?";
        $params[] = intval($year_filter);
    }

    // Order by year and power for consistent results
    $sql .= " ORDER BY i.an_installation DESC, i.puissance_crete DESC LIMIT " . $limit;

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transform data to match the required format
    $solarInstallations = [];
    
    foreach ($results as $row) {
        // Skip invalid coordinates
        if (!$row['lat'] || !$row['lon'] || $row['lat'] == 0 || $row['lon'] == 0) {
            continue;
        }
        
        // Format power with unit
        $power_formatted = $row['power'] ? number_format($row['power'], 1) . ' kW' : 'N/A';
        
        // Use city name if available, otherwise use postal code or department
        $city = $row['city_name'] ?: 
                ($row['code_postal'] ? 'CP ' . $row['code_postal'] : 
                ($row['department_name'] ?: 'Localisation inconnue'));
        
        $installation = [
            'id' => intval($row['id']),
            'lat' => floatval($row['lat']),
            'lng' => floatval($row['lon']),
            'city' => $city,
            'year' => intval($row['year']),
            'department' => intval($row['department_code']),
            'department_name' => $row['department_name'],
            'region' => intval($row['region_code']),
            'region_name' => $row['region_name'],
            'power' => $power_formatted,
            'power_numeric' => floatval($row['power']),
            'postal_code' => $row['code_postal'],
            'insee_code' => $row['code_insee']
        ];
        
        $solarInstallations[] = $installation;
    }

    // Return the data
    echo json_encode([
        'success' => true,
        'data' => $solarInstallations,
        'count' => count($solarInstallations),
        'total_in_db' => count($results),
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
        'error' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode()
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