# Distance Excel/CSV Upload API

This API allows you to upload Excel files (.xlsx, .xls) or CSV files containing distance data with the following fields:

-   from (starting location)
-   to (destination location)
-   trip (trip name/route description)
-   distance (distance in kilometers)

## API Endpoints

### 1. Upload Distance Excel/CSV File

**POST** `/api/distances/upload-distance-data-excel`

**Parameters:**

-   `excel_file` (file, required): Excel file (.xlsx, .xls) or CSV file (.csv) containing distance data

**File Format:**
The file should have the following column headers in the first row:

-   from
-   to
-   trip
-   distance

**Example Request:**

```bash
# For Excel file
curl -X POST http://your-domain/api/distances/upload-distance-data-excel \
  -F "excel_file=@distances.xlsx"

# For CSV file
curl -X POST http://your-domain/api/distances/upload-distance-data-excel \
  -F "excel_file=@distances.csv"
```

**Success Response (200):**

```json
{
    "status": 200,
    "success": true,
    "message": "Import completed. 18 distances imported, 0 failed.",
    "data": {
        "total_rows": 18,
        "imported": 18,
        "failed": 0,
        "errors": []
    }
}
```

### 2. Get Route Distance

**POST** `/api/distances/get-route-distance`

Get distance between two specific locations.

**Parameters:**

```json
{
    "from_location": "MANGAON",
    "to_location": "PARADEEP"
}
```

**Response:**

```json
{
    "status": 200,
    "success": true,
    "message": "Route distance found successfully",
    "data": {
        "from_location": "MANGAON",
        "to_location": "PARADEEP",
        "distance": "1900.00",
        "formatted_distance": "1900.00 KM",
        "trip_name": "MANGAON-PARADEEP"
    }
}
```

### 3. Download Template

**GET** `/api/distances/download-template`

Downloads a sample CSV template for distance import.

### 4. Distance CRUD Operations

#### Get All Distances

**GET** `/api/distances`

#### Create Distance

**POST** `/api/distances`

```json
{
    "from_location": "MANGAON",
    "to_location": "PARADEEP",
    "trip_name": "MANGAON-PARADEEP",
    "distance": 1900
}
```

#### Get Distance by ID

**GET** `/api/distances/{id}`

#### Update Distance

**PUT** `/api/distances/{id}`

#### Delete Distance

**DELETE** `/api/distances/{id}`

#### Search Distances

**GET** `/api/distances/search?from_location=MANGAON&to_location=PARADEEP`

## Validation Rules

-   **from_location**: Required, string, max 255 characters
-   **to_location**: Required, string, max 255 characters
-   **trip_name**: Required, string, max 255 characters
-   **distance**: Required, numeric, minimum 0

## Features

-   **Automatic Uppercase Conversion**: All location names are automatically converted to uppercase
-   **Duplicate Prevention**: Prevents duplicate routes (same from_location and to_location)
-   **Reverse Route Search**: Can find distance even if route is stored in reverse direction
-   **Flexible Search**: Search by from/to locations or trip name
-   **BOM Support**: Handles Excel files with BOM characters

## Notes

-   Maximum file size: 10MB
-   Supported formats: .xlsx, .xls, .csv
-   Duplicate routes (same from and to locations) will be skipped
-   All validation errors are returned with specific row numbers
-   Location names are stored in uppercase for consistency

## Sample Data Format

| from      | to       | trip               | distance |
| --------- | -------- | ------------------ | -------- |
| MANGAON   | PARADEEP | MANGAON-PARADEEP   | 1900     |
| DANKUNI   | KHURDA   | DANKUNI-KHURDA     | 475      |
| AHMEDABAD | MALIA    | AHMEDABAD-MALIA    | 160      |
| KHIRASARA | CHHATRAL | KHIRASARA-CHHATRAL | 250      |

## Example Usage

### Upload Distance Data

```bash
POST /api/distances/upload-distance-data-excel
Content-Type: multipart/form-data
Body: excel_file=@your_distances.csv
```

### Find Route Distance

```bash
POST /api/distances/get-route-distance
Content-Type: application/json
{
  "from_location": "MANGAON",
  "to_location": "PARADEEP"
}
```

### Search Distances

```bash
GET /api/distances/search?from_location=MANGAON
```
