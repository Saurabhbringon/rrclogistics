# Vehicle Excel/CSV Upload API

This API allows you to upload Excel files (.xlsx, .xls) or CSV files containing vehicle data with the following fields:

-   vehical_number
-   company
-   vehical_series
-   fuel_type

## API Endpoints

### 1. Upload Vehicle Excel/CSV File

**POST** `/api/vehicles/upload-vehicle-data-excel`

**Parameters:**

-   `excel_file` (file, required): Excel file (.xlsx, .xls) or CSV file (.csv) containing vehicle data

**File Format:**
The file should have the following column headers in the first row:

-   vehical_number
-   company
-   vehical_series
-   fuel_type

**Example Request:**

```bash
# For Excel file
curl -X POST http://your-domain/api/vehicles/upload-vehicle-data-excel \
  -F "excel_file=@vehicles.xlsx"

# For CSV file
curl -X POST http://your-domain/api/vehicles/upload-vehicle-data-excel \
  -F "excel_file=@vehicles.csv"
```

**Success Response (200):**

```json
{
    "status": 200,
    "success": true,
    "message": "Import completed. 20 vehicles imported, 0 failed.",
    "data": {
        "total_rows": 20,
        "imported": 20,
        "failed": 0,
        "errors": []
    }
}
```

### 2. Download Template

**GET** `/api/vehicles/download-template`

Downloads a sample CSV template for vehicle import.

### 3. Vehicle CRUD Operations

#### Get All Vehicles

**GET** `/api/vehicles`

#### Create Vehicle

**POST** `/api/vehicles`

```json
{
    "vehical_number": "MH43BP8938",
    "company": "RRCLPL",
    "vehical_series": "1",
    "fuel_type": "Diesel"
}
```

#### Get Vehicle by ID

**GET** `/api/vehicles/{id}`

#### Update Vehicle

**PUT** `/api/vehicles/{id}`

#### Delete Vehicle

**DELETE** `/api/vehicles/{id}`

#### Get Active Vehicles

**GET** `/api/vehicles/active`

#### Get Inactive Vehicles

**GET** `/api/vehicles/inactive`

#### Update Vehicle Status

**PATCH** `/api/vehicles/{id}/status`

```json
{
    "status": "1"
}
```

#### Search Vehicles

**GET** `/api/vehicles/search?vehical_number=MH43&company=RRC&fuel_type=Diesel`

## Validation Rules

-   **vehical_number**: Required, string, max 255 characters, must be unique
-   **company**: Required, string, max 255 characters
-   **vehical_series**: Optional, string, max 50 characters
-   **fuel_type**: Required, string, max 100 characters

## Notes

-   Maximum file size: 10MB
-   Supported formats: .xlsx, .xls, .csv
-   Duplicate vehicles (same vehical_number) will be skipped
-   All validation errors are returned with specific row numbers
-   The API will process all rows and return a summary of successes and failures

## Sample Data Format

| vehical_number | company | vehical_series | fuel_type |
| -------------- | ------- | -------------- | --------- |
| MH43BP8938     | RRCLPL  | 1              | Diesel    |
| MH43BP8939     | RRCLPL  | 1              | Diesel    |
| GJ12BW7502     | RRCLPL  | 2              | Diesel    |
| GJ12BW7378     | RRC     | 3              | Diesel    |
